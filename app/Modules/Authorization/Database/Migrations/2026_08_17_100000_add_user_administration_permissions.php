<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var list<string> */
    private array $permissions = [
        'users.manage-roles',
        'users.manage-super-admin',
        'users.view',
    ];

    public function up(): void
    {
        $preexistingPermissionIds = $this->permissionIds();

        if ($this->hasUnsafeAuthorizationState($preexistingPermissionIds)) {
            throw new RuntimeException('Pre-existing user administration permissions require drift review.');
        }

        $now = now();

        foreach ($this->permissions as $permission) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permissionIds = $this->permissionIds();
        $roleIds = DB::table('roles')
            ->where('guard_name', 'web')
            ->whereIn('name', ['admin', 'super-admin'])
            ->pluck('id', 'name');

        throw_unless(count($permissionIds) === 3 && $roleIds->count() === 2, 'The authorization catalog is incomplete.');

        $mappings = [
            'admin' => ['users.manage-roles', 'users.view'],
            'super-admin' => $this->permissions,
        ];

        foreach ($mappings as $role => $permissions) {
            foreach ($permissions as $permission) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'role_id' => $roleIds[$role],
                    'permission_id' => $permissionIds[$permission],
                ]);
            }
        }

        $this->clearPermissionCache();
    }

    public function down(): void
    {
        $permissionIds = $this->permissionIds();

        if ($this->hasUnsafeAuthorizationState($permissionIds)) {
            throw new RuntimeException('User administration rollback would discard authorization state.');
        }

        DB::table('permissions')->whereIn('id', array_values($permissionIds))->delete();
        $this->clearPermissionCache();
    }

    /** @return array<string, int> */
    private function permissionIds(): array
    {
        return DB::table('permissions')
            ->where('guard_name', 'web')
            ->whereIn('name', $this->permissions)
            ->pluck('id', 'name')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    /** @param array<string, int> $permissionIds */
    private function hasUnsafeAuthorizationState(array $permissionIds): bool
    {
        if ($permissionIds === []) {
            return false;
        }

        $ids = array_values($permissionIds);
        $directAssignments = DB::table('model_has_permissions')
            ->whereIn('permission_id', $ids)
            ->exists();

        if ($directAssignments) {
            return true;
        }

        $manageSuperAdminId = $permissionIds['users.manage-super-admin'] ?? null;

        return DB::table('role_has_permissions')
            ->join('roles', 'roles.id', '=', 'role_has_permissions.role_id')
            ->whereIn('role_has_permissions.permission_id', $ids)
            ->where(function ($query) use ($manageSuperAdminId): void {
                $query->whereNotIn('roles.name', ['admin', 'super-admin'])
                    ->orWhere('roles.guard_name', '!=', 'web');

                if ($manageSuperAdminId !== null) {
                    $query->orWhere(function ($query) use ($manageSuperAdminId): void {
                        $query->where('roles.name', 'admin')
                            ->where('role_has_permissions.permission_id', $manageSuperAdminId);
                    });
                }
            })
            ->exists();
    }

    private function clearPermissionCache(): void
    {
        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }
};
