<?php

namespace App\Notifications;

use App\Mail\TemplatedMail;
use App\Support\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Alerts the users assigned to the stage an application has just arrived at,
 * telling them it was passed (from the previous stage) or rejected (sent back).
 *
 * Constructed with plain scalars — no models — so it serialises cleanly on the
 * queue even if the underlying records change before it's delivered.
 */
class ApplicationStageChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $applicationId,
        public string $projectTitle,
        public string $decision,        // 'passed' | 'rejected'
        public string $fromStageLabel,  // stage the decision was made on
        public string $toStageLabel,    // stage it now sits at (the recipients')
        public ?string $note = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): TemplatedMail
    {
        $verb = $this->decision === 'rejected' ? 'rejected' : 'passed';
        $link = rtrim((string) config('app.frontend_url'), '/') . '/applications/' . $this->applicationId;

        $note = trim((string) $this->note);
        $noteHtml = $note !== ''
            ? '<tr><td style="padding:6px 0;color:#6b7280;width:160px;vertical-align:top;">Note</td>'
              . '<td style="padding:6px 0;white-space:pre-wrap;">' . e($note) . '</td></tr>'
            : '';
        $noteText = $note !== '' ? "Note: {$note}\n" : '';

        $rendered = EmailTemplate::render('application-stage-changed', [
            'PROJECT_TITLE' => $this->projectTitle,
            'DECISION'      => $verb,
            'FROM_STAGE'    => $this->fromStageLabel,
            'TO_STAGE'      => $this->toStageLabel,
            'NOTE_BLOCK'    => $noteHtml,
            'NOTE_TEXT'     => $noteText,
            'LINK'          => $link,
        ]);

        $subject = "Application {$verb}: {$this->projectTitle}";

        return (new TemplatedMail($subject, $rendered['html'], $rendered['text']))
            ->to($notifiable->routeNotificationFor('mail'));
    }
}
