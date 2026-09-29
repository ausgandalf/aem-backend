<?php

namespace App\Notifications;

use App\Mail\TemplatedMail;
use App\Models\Application;
use App\Support\EmailTemplate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Single onboarding email for a NEW applicant created via Quick Apply.
 * Combines what used to be three emails (verify + reset + application-received)
 * into one "welcome, your draft is saved, set your password" message. The
 * set-password link is a real password-reset link; completing it also verifies
 * the email (see AuthController::resetPassword).
 */
class QuickApplyWelcome extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public function __construct(string $token, public Application $application)
    {
        parent::__construct($token);
    }

    public function toMail($notifiable): TemplatedMail
    {
        $expire = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        $rendered = EmailTemplate::render('quick-apply-welcome', [
            'NAME'          => $notifiable->first_name ?? '',
            'PROJECT_TITLE' => $this->application->project_title,
            'ACTION_URL'    => $this->resetUrl($notifiable),
            'EXPIRE'        => $expire,
        ]);

        return (new TemplatedMail('Welcome to WRBLO ARM — set your password', $rendered['html'], $rendered['text']))
            ->to($notifiable->routeNotificationFor('mail'));
    }
}
