<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // GET /api/users - list with search + pagination
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->with('organization')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('first_name', 'ilike', "%{$request->search}%")
                      ->orWhere('last_name', 'ilike', "%{$request->search}%")
                      ->orWhere('email', 'ilike', "%{$request->search}%")
                      ->orWhere('phone', 'ilike', "%{$request->search}%");
                });
            })
            ->when($request->role, fn ($q) => $q->where('role', $request->role))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json($users);
    }

    // GET /api/users/{user} - single user with recent logs
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => $user->load('organization'),
            'roles' => $user->getRoleNames(),
            'recent_logs' => $user->logs()->latest('created_at')->limit(10)->get(),
        ]);
    }

    // GET /api/users/{user}/logs - paginated logs
    public function logs(User $user, Request $request): JsonResponse
    {
        $logs = $user->logs()
            ->with('actionedBy:id,first_name,last_name')
            ->latest('created_at')
            ->paginate($request->per_page ?? 20);

        return response()->json($logs);
    }

    // PATCH /api/users/{user}/allow
    public function allow(User $user): JsonResponse
    {
        $user->update(['status' => 'active']);

        UserLog::create([
            'user_id'     => $user->id,
            'action'      => 'allowed',
            'actioned_by' => auth()->id(),
            'details'     => 'User approved by admin',
        ]);

        return response()->json([
            'message' => 'User activated successfully',
            'user'    => $user,
        ]);
    }

    // PATCH /api/users/{user}/block
    public function block(User $user): JsonResponse
    {
        // Prevent admin blocking themselves
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot block yourself'
            ], 422);
        }

        $user->update(['status' => 'blocked']);

        UserLog::create([
            'user_id'     => $user->id,
            'action'      => 'blocked',
            'actioned_by' => auth()->id(),
            'details'     => 'User blocked by admin',
        ]);

        return response()->json([
            'message' => 'User blocked successfully',
            'user'    => $user,
        ]);
    }

    // PATCH /api/users/{user} - edit user details
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'first_name'  => ['sometimes', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name'   => ['sometimes', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'role'        => ['sometimes', Rule::in([
                'applicant', 'evaluator', 'pcmu_officer', 'eco_analyst',
                'approvals_officer', 'board_member', 'dcf_officer',
                'finance_officer', 'admin', 'marketing',
            ])],
            'organization_id' => ['nullable', 'exists:organizations,id'],
        ]);

        $oldRole = $user->role;
        $user->update($validated);

        // Sync Spatie role if role changed
        if (isset($validated['role']) && $validated['role'] !== $oldRole) {
            $user->syncRoles([$validated['role']]);

            UserLog::create([
                'user_id'     => $user->id,
                'action'      => 'role-changed',
                'actioned_by' => auth()->id(),
                'details'     => "Role changed from {$oldRole} to {$validated['role']}",
            ]);
        }

        UserLog::create([
            'user_id'     => $user->id,
            'action'      => 'updated',
            'actioned_by' => auth()->id(),
            'details'     => 'User details updated by admin',
        ]);

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => $user->fresh(),
        ]);
    }
}