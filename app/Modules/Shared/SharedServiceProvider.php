<?php

namespace App\Modules\Shared;

use Illuminate\Support\ServiceProvider;

class SharedServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register shared services, repositories, etc.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Shared module doesn't have routes - just provides shared resources
    }
}
