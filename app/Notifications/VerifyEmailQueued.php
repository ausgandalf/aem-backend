<?php

namespace App\Notifications;

use App\Mail\TemplatedMail;
use App\Support\EmailTemplate;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

// Queued email-verification notification rendered through our own templates.
class VerifyEmailQueued extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    public function toMail($notifiable): TemplatedMail
    {
        $rendered = EmailTemplate::render('verify-email', [
            'NAME'       => $notifiable->first_name ?? '',
            'ACTION_URL' => $this->verificationUrl($notifiable),
        ]);

        return (new TemplatedMail('Verify your email address', $rendered['html'], $rendered['text']))
            ->to($notifiable->routeNotificationFor('mail'));
    }
}
