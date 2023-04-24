<?php

namespace App\Providers;

use App\Models\Planning;
use App\Services\PlanningService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PlanningService::class, function ($app) {
            return new PlanningService(new Planning());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
