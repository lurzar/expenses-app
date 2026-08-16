<?php

namespace App\Modules\Admin;

use App\Modules\Admin\Navigation\AdminNavigationItem;
use App\Modules\Admin\Navigation\AdminNavigationRegistry;
use App\Modules\Authorization\Permissions\SystemPermission;
use Illuminate\Support\ServiceProvider;

final class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AdminNavigationRegistry::class, function (): AdminNavigationRegistry {
            $registry = new AdminNavigationRegistry;
            $registry->register(new AdminNavigationItem(
                key: 'overview',
                labelKey: 'admin.overview',
                descriptionKey: 'admin.overview_description',
                routeName: 'admin.index',
                ability: SystemPermission::AccessAdmin->value,
            ));
            $registry->register(new AdminNavigationItem(
                key: 'users',
                labelKey: 'admin.users',
                descriptionKey: 'admin.users_description',
                routeName: 'admin.users.index',
                ability: SystemPermission::ViewUsers->value,
            ));

            return $registry;
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
