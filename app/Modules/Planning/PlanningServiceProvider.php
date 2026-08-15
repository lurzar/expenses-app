<?php

namespace App\Modules\Planning;

use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Planning\Models\Planning;
use App\Modules\Planning\Services\PlanningService;
use Illuminate\Support\ServiceProvider;

class PlanningServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PlanningService::class, function ($app) {
            return new PlanningService(new Planning, $app->make(ActivityRecorder::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }
}
