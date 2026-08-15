<?php

namespace App\Modules\Planning;

use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Planning\Models\Planning;
use App\Modules\Planning\Policies\PlanningPolicy;
use App\Modules\Planning\Services\PlanningCache;
use App\Modules\Planning\Services\PlanningService;
use App\Modules\Planning\Support\PlanningCalculator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PlanningServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PlanningService::class, function ($app) {
            return new PlanningService(
                new Planning,
                $app->make(ActivityRecorder::class),
                $app->make(PlanningCache::class),
                $app->make(PlanningCalculator::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Planning::class, PlanningPolicy::class);

        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }
}
