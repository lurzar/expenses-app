<?php

namespace App\Modules\Planning;

use Illuminate\Support\ServiceProvider;
use App\Modules\Planning\Models\Planning;
use App\Modules\Planning\Observers\PlanningObserver;
use App\Modules\Planning\Services\PlanningService;

class PlanningServiceProvider extends ServiceProvider
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
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        
        Planning::observe(PlanningObserver::class);
    }
}
