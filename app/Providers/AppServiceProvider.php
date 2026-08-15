<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Providers\TelescopeServiceProvider as AppTelescopeServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Telescope\TelescopeServiceProvider;
use LogicException;

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
    private function customGuessFactoryNames(): void
    {
        Factory::guessFactoryNamesUsing(fn (string $modelName): string => $this->resolveFactoryName($modelName));
    }

    /**
     * Custom guess factory model names for modules
     * Result: App\Modules\Planning\Database\Factories\PlanningFactory
     * Fallback: App\Models\Planning
     */
    private function customGuessFactoryModelNames(): void
    {
        Factory::guessModelNamesUsing(fn (Factory $factory): string => $this->resolveFactoryModelName($factory));
    }

    /**
     * @param  class-string<Model>  $modelName
     * @return class-string<Factory<Model>>
     */
    private function resolveFactoryName(string $modelName): string
    {
        if (str_contains($modelName, 'App\\Modules\\')) {
            $moduleNamespace = Str::before($modelName, 'Models\\');
            $modelBasename = class_basename($modelName);
            $factoryName = $moduleNamespace.'Database\\Factories\\'.$modelBasename.'Factory';
        } else {
            $modelBasename = Str::afterLast($modelName, '\\');
            $factoryName = 'Database\\Factories\\'.$modelBasename.'Factory';
        }

        if (! is_subclass_of($factoryName, Factory::class)) {
            throw new LogicException("Unable to resolve factory for {$modelName}.");
        }

        return $factoryName;
    }

    /**
     * @param  Factory<Model>  $factory
     * @return class-string<Model>
     */
    private function resolveFactoryModelName(Factory $factory): string
    {
        $factoryClass = get_class($factory);
        $namespacedFactoryBasename = Str::replaceLast(
            'Factory', '', Str::replaceFirst('Database\\Factories\\', '', $factoryClass)
        );
        $factoryBasename = Str::afterLast($namespacedFactoryBasename, '\\');

        if (str_contains($factoryClass, 'App\\Modules\\')) {
            $moduleNamespace = Str::before($factoryClass, 'Database\\Factories\\');
            $modelName = $moduleNamespace.'Models\\'.$factoryBasename;
        } else {
            $modelName = 'App\\Models\\'.$factoryBasename;
        }

        if (! is_subclass_of($modelName, Model::class)) {
            throw new LogicException("Unable to resolve model for {$factoryClass}.");
        }

        return $modelName;
    }
}
