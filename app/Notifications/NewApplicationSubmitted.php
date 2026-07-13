<?php

namespace App\Notifications;

use App\Mail\TemplatedMail;
use App\Models\Application;
use App\Support\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Application $application,
        public string $applicantName,
        public string $organizationName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): TemplatedMail
    {
        $amount = $this->application->requested_amount
            ? number_format((float) $this->application->requested_amount, 2) . ' ' . $this->application->currency
            : 'N/A';

        $rendered = EmailTemplate::render('new-application-submitted', [
            'APPLICANT_NAME' => $this->applicantName,
            'ORG_NAME'       => $this->organizationName,
            'PROJECT_TITLE'  => $this->application->project_title,
            'AMOUNT'         => $amount,
            'LOCATION'       => $this->application->project_location ?? 'N/A',
        ]);

        return (new TemplatedMail('New application submitted', $rendered['html'], $rendered['text']))
            ->to($notifiable->routeNotificationFor('mail'));
    }
}
