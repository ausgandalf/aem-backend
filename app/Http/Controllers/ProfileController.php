<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    // PATCH /api/profile - update the authenticated user's own profile
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name'          => ['required', 'string', 'max:255'],
            'middle_name'         => ['nullable', 'string', 'max:255'],
            'last_name'           => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone'               => ['nullable', 'string', 'max:20'],
            'preferred_contact'   => ['nullable', 'array'],
            'preferred_contact.*' => ['in:email,sms,scheduled_call'],
        ]);

        $emailChanged = $validated['email'] !== $user->email;

        $user->fill($validated);

        // Changing the email invalidates verification: force re-verify
        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        UserLog::create([
            'user_id'     => $user->id,
            'action'      => 'profile-updated',
            'actioned_by' => $user->id,
            'details'     => $emailChanged
                ? 'User updated their profile (email changed, re-verification required)'
                : 'User updated their profile',
        ]);

        return response()->json([
            'message' => $emailChanged
                ? 'Profile updated. Please verify your new email address.'
                : 'Profile updated successfully',
            'user'         => $user->fresh()->load('organization'),
            'email_changed' => $emailChanged,
        ]);
    }

    // PUT /api/password - change the authenticated user's password
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        UserLog::create([
            'user_id'     => $user->id,
            'action'      => 'password-changed',
            'actioned_by' => $user->id,
            'details'     => 'User changed their password',
        ]);

        return response()->json([
            'message' => 'Password changed successfully',
        ]);
    }
}
