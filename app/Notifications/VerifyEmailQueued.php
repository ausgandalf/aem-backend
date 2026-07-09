<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

// Queued version of Laravel's email-verification notification so it sends async.
class VerifyEmailQueued extends VerifyEmail implements ShouldQueue
{
    use Queueable;
}
