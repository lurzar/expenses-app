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
                key: 'roles',
                labelKey: 'admin.roles',
                descriptionKey: 'admin.roles_description',
                routeName: 'admin.roles.index',
                ability: SystemPermission::ManageRoles->value,
            ));

            return $registry;
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
