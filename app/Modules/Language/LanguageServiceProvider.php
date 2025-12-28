<?php

namespace App\Modules\Language;

use Illuminate\Support\ServiceProvider;

class LanguageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        
        // Override Laravel's default lang path to use our module's Dictionaries folder
        $this->app->useLangPath(__DIR__.'/Dictionaries');
    }
}
