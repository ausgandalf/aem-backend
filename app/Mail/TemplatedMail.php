<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

/**
 * Generic mailable that ships pre-rendered HTML + plain-text bodies produced by
 * App\Support\EmailTemplate. It sets the bodies directly (no Blade views), so the
 * template files are the single source of email content.
 */
class TemplatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $htmlBody,
        public string $textBody,
    ) {}

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->html($this->htmlBody)
            ->withSymfonyMessage(function (Email $email) {
                $email->text($this->textBody);

                // Embed the logo inline (cid) so it renders for real recipients —
                // remote (especially localhost) URLs are unreachable for them
                // and for the Gmail/Outlook image proxies.
                $logo = config('mail.logo_path');
                if ($logo && is_file($logo) && str_contains($this->htmlBody, 'cid:wrblo-logo')) {
                    $email->embedFromPath($logo, 'wrblo-logo');
                }
            });
    }
}
