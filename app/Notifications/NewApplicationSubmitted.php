<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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

    public function toMail(object $notifiable): MailMessage
    {
        $amount = $this->application->requested_amount
            ? number_format((float) $this->application->requested_amount, 2) . ' ' . $this->application->currency
            : 'N/A';

        return (new MailMessage)
            ->subject('New application submitted')
            ->line('A new application has been submitted via Quick Apply.')
            ->line("Applicant: {$this->applicantName}")
            ->line("Organization: {$this->organizationName}")
            ->line("Project: {$this->application->project_title}")
            ->line("Requested amount: {$amount}")
            ->line("Location: " . ($this->application->project_location ?? 'N/A'));
    }
}
