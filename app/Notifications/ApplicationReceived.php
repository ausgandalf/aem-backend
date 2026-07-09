<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('We received your application')
            ->greeting("Hello {$notifiable->first_name},")
            ->line('Thank you for submitting your application to WRBLO.')
            ->line("Project: {$this->application->project_title}")
            ->line('Our team will review it and keep you updated on its progress.')
            ->line('If you have not already, please confirm your email address using the separate verification link we sent you.')
            ->line('Thank you for your interest in WRBLO.');
    }
}
