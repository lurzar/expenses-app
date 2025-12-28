<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Service bindings are handled by module ServiceProviders
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Email verification listener (moved from EventServiceProvider)
        Event::listen(
            Registered::class,
            SendEmailVerificationNotification::class,
        );
    }
}
