<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Password reset emails should link to the frontend SPA, not a backend route
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $email = urlencode($notifiable->getEmailForPasswordReset());
            return config('app.frontend_url') . "/reset-password?token={$token}&email={$email}";
        });
    }
}
