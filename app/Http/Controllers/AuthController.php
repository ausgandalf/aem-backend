<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name'=> ['nullable', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'unique:users'],
            'password'   => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'phone'      => ['required', 'string', 'max:20'],
            'role'       => ['required', Rule::in([
                'applicant',
                'evaluator',
                'pcmu_officer',
                'eco_analyst',
                'approvals_officer',
                'board_member',
                'dcf_officer',
                'finance_officer',
            ])],
            'organization_id'   => ['nullable', 'exists:organizations,id'],
            'referred_from'     => ['nullable', 'string', 'max:255'],
            'preferred_contact' => ['nullable', 'array'],
            'preferred_contact.*' => ['in:email,sms,scheduled_call'],
        ]);

        $user = User::create([
            ...$validated,
            'password'          => Hash::make($validated['password']),
            'status'            => 'pending',
            'preferred_contact' => $validated['preferred_contact'] ?? ['email'],
        ]);

        // Assign Spatie role
        $user->assignRole($validated['role']);

        // Send verification email
        $user->sendEmailVerificationNotification();

        // Log signup
        UserLog::create([
            'user_id' => $user->id,
            'action'  => 'signup',
            'details' => 'User registered with role: ' . $validated['role'],
        ]);

        return response()->json([
            'message' => 'Registration successful. Please check your email to verify your account.',
        ], 201);
    }

    // LOGIN
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        // Check email verified
        if (!$user->hasVerifiedEmail()) {
            Auth::logout();
            return response()->json([
                'message' => 'Please verify your email before logging in'
            ], 403);
        }

        // Check user is active
        if ($user->status !== 'active') {
            Auth::logout();
            return response()->json([
                'message' => match($user->status) {
                    'pending' => 'Your account is pending admin approval',
                    'blocked' => 'Your account has been blocked',
                    default   => 'Your account is not active',
                }
            ], 403);
        }

        // Log login
        UserLog::create([
            'user_id' => $user->id,
            'action'  => 'login',
            'details' => 'User logged in from IP: ' . $request->ip(),
        ]);

        return response()->json([
            'message' => 'Login successful',
            'user'    => $user,
            'roles'   => $user->getRoleNames(),
        ]);
    }

    // LOGOUT
    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Log logout
        UserLog::create([
            'user_id' => $user->id,
            'action'  => 'logout',
            'details' => 'User logged out',
        ]);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    // ME
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user'  => $request->user()->load('organization'),
            'roles' => $request->user()->getRoleNames(),
            'permissions' => $request->user()->getAllPermissions()->pluck('name'),
        ]);
    }

    // FORGOT PASSWORD - email a reset link
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        // Sends the reset link if the email exists; ignores the result on purpose
        PasswordBroker::sendResetLink($request->only('email'));

        // Always generic so we don't reveal which emails have accounts
        return response()->json([
            'message' => 'If an account exists for that email, a reset link has been sent.',
        ]);
    }

    // RESET PASSWORD - set a new password using the emailed token
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $status = PasswordBroker::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->setRememberToken(Str::random(60));
                $user->save();

                UserLog::create([
                    'user_id' => $user->id,
                    'action'  => 'password-reset',
                    'details' => 'Password reset via email link',
                ]);

                event(new PasswordReset($user));
            }
        );

        if ($status === PasswordBroker::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password has been reset. You can now sign in.',
            ]);
        }

        // e.g. invalid/expired token or unknown email
        return response()->json(['message' => __($status)], 422);
    }
}