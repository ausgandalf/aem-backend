<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    // GET /api/admin/organizations
    public function index(Request $request): JsonResponse
    {
        $organizations = Organization::query()
            ->withCount('users')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('name', 'ilike', "%{$request->search}%")
                      ->orWhere('register_no', 'ilike', "%{$request->search}%")
                      ->orWhere('type', 'ilike', "%{$request->search}%");
                });
            })
            ->orderBy('name')
            ->paginate($request->per_page ?? 20);

        return response()->json($organizations);
    }

    // POST /api/admin/organizations
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateOrganization($request);

        $organization = Organization::create($validated);

        return response()->json([
            'message'      => 'Organization created successfully',
            'organization' => $organization,
        ], 201);
    }

    // GET /api/admin/organizations/{organization}
    public function show(Organization $organization): JsonResponse
    {
        $organization->loadCount('users');

        return response()->json(['organization' => $organization]);
    }

    // PATCH /api/admin/organizations/{organization}
    public function update(Request $request, Organization $organization): JsonResponse
    {
        $validated = $this->validateOrganization($request);

        $organization->update($validated);

        return response()->json([
            'message'      => 'Organization updated successfully',
            'organization' => $organization->fresh(),
        ]);
    }

    // DELETE /api/admin/organizations/{organization}
    public function destroy(Organization $organization): JsonResponse
    {
        if ($organization->users()->exists()) {
            return response()->json([
                'message' => 'Cannot delete an organization that has users assigned',
            ], 422);
        }

        $organization->delete();

        return response()->json([
            'message' => 'Organization deleted successfully',
        ]);
    }

    private function validateOrganization(Request $request): array
    {
        return $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'country'      => ['required', 'string', 'size:2'],
            'type'         => ['nullable', 'string', 'max:255'],
            'note'         => ['nullable', 'string'],
            'legal_status' => ['nullable', 'string', 'max:255'],
            'register_no'  => ['nullable', 'string', 'max:255'],
        ]);
    }
}