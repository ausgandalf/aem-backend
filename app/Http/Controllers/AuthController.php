<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    // Minimum wait between reset-link / verification-email requests for one email.
    private const EMAIL_COOLDOWN = 300; // 5 minutes

    // Seconds remaining on a per-email cooldown (0 = clear to send).
    private function cooldownRemaining(string $key): int
    {
        $until = Cache::get($key);

        return $until ? max(0, $until - now()->timestamp) : 0;
    }

    private function startCooldown(string $key): void
    {
        Cache::put($key, now()->timestamp + self::EMAIL_COOLDOWN, self::EMAIL_COOLDOWN);
    }
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

    // FORGOT PASSWORD - email a reset link (max once per 5 minutes per email)
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        $email = strtolower(trim($request->email));
        $key = 'pw-reset-cooldown:' . sha1($email);

        // Cooldown is applied uniformly (even for unknown emails) so the response
        // and timing never reveal whether an account exists.
        $remaining = $this->cooldownRemaining($key);
        if ($remaining > 0) {
            return response()->json([
                'message'     => 'A reset link was requested recently. Please wait before trying again.',
                'retry_after' => $remaining,
            ], 429);
        }

        PasswordBroker::sendResetLink(['email' => $email]);
        $this->startCooldown($key);

        // Always generic so we don't reveal which emails have accounts
        return response()->json([
            'message'     => 'If an account exists for that email, a reset link has been sent.',
            'retry_after' => self::EMAIL_COOLDOWN,
        ]);
    }

    // RESEND VERIFICATION - public (unverified users can't log in to reach the
    // authenticated resend). Max once per 5 minutes per email; response is generic.
    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        $email = strtolower(trim($request->email));
        $key = 'verify-resend-cooldown:' . sha1($email);

        $remaining = $this->cooldownRemaining($key);
        if ($remaining > 0) {
            return response()->json([
                'message'     => 'A verification email was sent recently. Please wait before trying again.',
                'retry_after' => $remaining,
            ], 429);
        }

        $user = User::where('email', $email)->first();
        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
        $this->startCooldown($key);

        return response()->json([
            'message'     => 'If your account still needs verification, a new link has been sent.',
            'retry_after' => self::EMAIL_COOLDOWN,
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
                $fill = ['password' => Hash::make($request->password)];

                // Completing an emailed reset link proves the person owns the inbox,
                // so verify the email here too. This lets new Quick Apply users onboard
                // from a SINGLE email (set password → verified → can sign in).
                if (! $user->hasVerifiedEmail()) {
                    $fill['email_verified_at'] = now();
                }

                $user->forceFill($fill)->setRememberToken(Str::random(60));
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