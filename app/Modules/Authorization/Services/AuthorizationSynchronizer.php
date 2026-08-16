<?php

namespace App\Modules\Authorization\Services;

use App\Modules\ActivityLog\ActivityEvent;
use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Authorization\AuthorizationSyncResult;
use App\Modules\Authorization\PermissionCatalog;
use App\Modules\Authorization\RoleName;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class AuthorizationSynchronizer
{
    public function __construct(
        private readonly PermissionCatalog $catalog,
        private readonly ActivityRecorder $activityRecorder,
        private readonly PermissionRegistrar $permissionRegistrar,
    ) {}

    public function sync(): AuthorizationSyncResult
    {
        $result = DB::transaction(function (): AuthorizationSyncResult {
            $createdPermissions = [];
            $createdRoles = [];
            $addedRolePermissions = [];

            foreach ($this->catalog->names() as $permissionName) {
                $permission = Permission::findOrCreate($permissionName, 'web');

                if ($permission->wasRecentlyCreated) {
                    $createdPermissions[] = $permissionName;
                }
            }

            foreach (RoleName::cases() as $roleName) {
                $role = Role::findOrCreate($roleName->value, 'web');

                if ($role->wasRecentlyCreated) {
                    $createdRoles[] = $roleName->value;
                }

                foreach ($roleName->permissions() as $permissionName) {
                    if (! $role->hasPermissionTo($permissionName)) {
                        $role->givePermissionTo($permissionName);
                        $addedRolePermissions[] = "{$roleName->value}:{$permissionName}";
                    }
                }
            }

            sort($createdPermissions);
            sort($createdRoles);
            sort($addedRolePermissions);

            $result = $this->result($createdPermissions, $createdRoles, $addedRolePermissions);

            $this->activityRecorder->record(
                ActivityEvent::AuthorizationCatalogSynchronized,
                null,
                'authorization_catalog',
                null,
                [
                    'created_permissions' => $createdPermissions,
                    'created_roles' => $createdRoles,
                    'added_role_permissions' => $addedRolePermissions,
                    'drift' => $result->drift(),
                ],
            );

            return $result;
        });

        $this->permissionRegistrar->forgetCachedPermissions();

        return $result;
    }

    /**
     * @param  list<string>  $createdPermissions
     * @param  list<string>  $createdRoles
     * @param  list<string>  $addedRolePermissions
     */
    private function result(array $createdPermissions, array $createdRoles, array $addedRolePermissions): AuthorizationSyncResult
    {
        $knownPermissions = $this->catalog->names();
        $knownRoles = array_map(fn (RoleName $role): string => $role->value, RoleName::cases());
        $unknownPermissions = array_values(Permission::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', $knownPermissions)
            ->orderBy('name')
            ->get()
            ->map(fn (Permission $permission): string => $permission->name)
            ->values()
            ->all());
        $unknownRoles = array_values(Role::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', $knownRoles)
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role): string => $role->name)
            ->values()
            ->all());
        $unexpectedRolePermissions = [];

        foreach (RoleName::cases() as $roleName) {
            $unexpected = Role::findByName($roleName->value, 'web')
                ->permissions
                ->pluck('name')
                ->diff($roleName->permissions())
                ->sort();

            foreach ($unexpected as $permissionName) {
                $unexpectedRolePermissions[] = "{$roleName->value}:{$permissionName}";
            }
        }

        sort($unexpectedRolePermissions);

        return new AuthorizationSyncResult(
            $createdPermissions,
            $createdRoles,
            $addedRolePermissions,
            $unknownPermissions,
            $unknownRoles,
            $unexpectedRolePermissions,
            DB::table(config('permission.table_names.model_has_permissions'))->count(),
        );
    }
}
