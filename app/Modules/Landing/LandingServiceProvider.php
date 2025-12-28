<?php

namespace App\Modules\Landing;

use Illuminate\Support\ServiceProvider;

class LandingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
