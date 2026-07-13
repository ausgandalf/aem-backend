<?php

namespace App\Notifications;

use App\Mail\TemplatedMail;
use App\Models\Application;
use App\Support\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ApplicationReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): TemplatedMail
    {
        $rendered = EmailTemplate::render('application-received', [
            'NAME'          => $notifiable->first_name,
            'PROJECT_TITLE' => $this->application->project_title,
            'LOGIN_URL'     => rtrim((string) config('app.frontend_url'), '/') . '/login',
        ]);

        return (new TemplatedMail('New application draft created', $rendered['html'], $rendered['text']))
            ->to($notifiable->routeNotificationFor('mail'));
    }
}
