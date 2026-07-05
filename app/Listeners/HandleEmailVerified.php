<?php

namespace App\Listeners;

use App\Models\UserLog;
use Illuminate\Auth\Events\Verified;

class HandleEmailVerified
{
    public function handle(Verified $event): void
    {
        $user = $event->user;

        // Log the verification
        UserLog::create([
            'user_id' => $user->id,
            'action'  => 'email-verified',
            'details' => 'User verified their email address',
        ]);

        // Auto-activate applicants only
        // Staff roles stay 'pending' until admin approves them
        if ($user->role === 'applicant') {
            $user->status = 'active';
            $user->save();

            UserLog::create([
                'user_id' => $user->id,
                'action'  => 'auto-activated',
                'details' => 'Applicant auto-activated after email verification',
            ]);
        }
    }
}