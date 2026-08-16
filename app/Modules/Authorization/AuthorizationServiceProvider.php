<?php

namespace App\Modules\Authorization;

use App\Models\User;
use App\Modules\Authorization\Console\SyncAuthorization;
use App\Modules\Authorization\Observers\AssignDefaultRole;
use App\Modules\Authorization\Permissions\SystemPermission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthorizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PermissionCatalog::class, function (): PermissionCatalog {
            $catalog = new PermissionCatalog;
            $catalog->register(SystemPermission::class);

            return $catalog;
        });
    }

    public function boot(): void
    {
        foreach ($this->app->make(PermissionCatalog::class)->names() as $permission) {
            Gate::define($permission, fn (User $user): bool => $user->hasPermissionTo($permission, 'web'));
        }

        User::observe(AssignDefaultRole::class);

        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadTranslationsFrom(__DIR__.'/Translations', 'authorization');

        if ($this->app->runningInConsole()) {
            $this->commands([SyncAuthorization::class]);
        }
    }
}
