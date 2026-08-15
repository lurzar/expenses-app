<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Providers\TelescopeServiceProvider as AppTelescopeServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Telescope\TelescopeServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $telescopeEnabled = $this->app->environment('local') || config('telescope.enabled') === true;

        if ($telescopeEnabled && class_exists(TelescopeServiceProvider::class)) {
            $this->app->register(TelescopeServiceProvider::class);
            $this->app->register(AppTelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->customGuessFactoryNames();
        $this->customGuessFactoryModelNames();

        // Email verification listener
        Event::listen(Registered::class, SendEmailVerificationNotification::class);

        // Register observers
        User::observe(UserObserver::class);
    }

    /**
     * Custom guess factory names for modules
     * Result: App\Modules\Planning\Database\Factories\PlanningFactory
     * Fallback: Database\Factories\PlanningFactory
     */
    private function customGuessFactoryNames()
    {
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            if (str_contains($modelName, 'App\\Modules\\')) {
                $moduleNamespace = Str::before($modelName, 'Models\\');
                $modelBasename = class_basename($modelName);

                return $moduleNamespace.'Database\\Factories\\'.$modelBasename.'Factory';
            }

            $modelName = Str::afterLast($modelName, '\\');

            return 'Database\\Factories\\'.$modelName.'Factory';
        });
    }

    /**
     * Custom guess factory model names for modules
     * Result: App\Modules\Planning\Database\Factories\PlanningFactory
     * Fallback: App\Models\Planning
     */
    private function customGuessFactoryModelNames()
    {
        Factory::guessModelNamesUsing(function (Factory $factory) {
            $namespacedFactoryBasename = Str::replaceLast(
                'Factory', '', Str::replaceFirst('Database\\Factories\\', '', get_class($factory))
            );

            $factoryBasename = Str::afterLast($namespacedFactoryBasename, '\\');

            if (str_contains(get_class($factory), 'App\\Modules\\')) {
                $moduleNamespace = Str::before(get_class($factory), 'Database\\Factories\\');

                return $moduleNamespace.'Models\\'.$factoryBasename;
            }

            return 'App\\Models\\'.$factoryBasename;
        });
    }
}
