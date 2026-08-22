<?php

namespace App\Modules\Authorization;

use App\Models\User;
use App\Modules\Authorization\Console\ManageSuperAdmin;
use App\Modules\Authorization\Console\SyncAuthorization;
use App\Modules\Authorization\Observers\AssignDefaultRole;
use App\Modules\Authorization\Permissions\SystemPermission;
use App\Modules\Authorization\Services\SuperAdminLifecycleService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class AuthorizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(SuperAdminLifecycleService::class);

        $this->app->singleton(PermissionCatalog::class, function (): PermissionCatalog {
            $catalog = new PermissionCatalog;
            $catalog->register(SystemPermission::class);

            return $catalog;
        });
    }

    public function boot(): void
    {
        foreach ($this->app->make(PermissionCatalog::class)->names() as $permission) {
            Gate::define($permission, function (User $user) use ($permission): bool {
                try {
                    return $user->hasPermissionTo($permission, 'web');
                } catch (PermissionDoesNotExist) {
                    return false;
                }
            });
        }

        User::observe(AssignDefaultRole::class);

        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadTranslationsFrom(__DIR__.'/Translations', 'authorization');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ManageSuperAdmin::class,
                SyncAuthorization::class,
            ]);
        }
    }
}
