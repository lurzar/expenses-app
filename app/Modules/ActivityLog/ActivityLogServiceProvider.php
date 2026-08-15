<?php

namespace App\Modules\ActivityLog;

use App\Modules\ActivityLog\Console\PruneActivityLogs;
use App\Modules\ActivityLog\Services\ActivityRecorder;
use Illuminate\Support\ServiceProvider;

class ActivityLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ActivityRecorder::class);
    }

    /**
     * Bootstrap activity-log services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([PruneActivityLogs::class]);
        }
    }
}
