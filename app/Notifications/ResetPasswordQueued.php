<?php

namespace App\Notifications;

use App\Mail\TemplatedMail;
use App\Support\EmailTemplate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

// Queued password-reset notification rendered through our own templates.
// The reset URL still comes from ResetPassword::createUrlUsing (AppServiceProvider).
class ResetPasswordQueued extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public function toMail($notifiable): TemplatedMail
    {
        $expire = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        $rendered = EmailTemplate::render('reset-password', [
            'NAME'       => $notifiable->first_name ?? '',
            'ACTION_URL' => $this->resetUrl($notifiable),
            'EXPIRE'     => $expire,
        ]);

        return (new TemplatedMail('Reset your password', $rendered['html'], $rendered['text']))
            ->to($notifiable->routeNotificationFor('mail'));
    }
}
