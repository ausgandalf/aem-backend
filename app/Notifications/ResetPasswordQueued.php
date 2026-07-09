<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

// Queued version of Laravel's password-reset notification so it sends async.
// The custom reset URL (ResetPassword::createUrlUsing in AppServiceProvider) is
// inherited via the shared static callback, so links still point to the frontend.
class ResetPasswordQueued extends ResetPassword implements ShouldQueue
{
    use Queueable;
}
