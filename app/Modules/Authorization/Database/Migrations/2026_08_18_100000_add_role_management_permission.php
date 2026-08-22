<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->insertOrIgnore([
            'name' => 'roles.manage',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permissionId = DB::table('permissions')
            ->where('name', 'roles.manage')
            ->where('guard_name', 'web')
            ->value('id');
        $roleId = DB::table('roles')
            ->where('name', 'super-admin')
            ->where('guard_name', 'web')
            ->value('id');

        throw_unless($permissionId !== null && $roleId !== null, 'The authorization catalog is incomplete.');

        DB::table('role_has_permissions')->insertOrIgnore([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
        ]);

        $this->clearPermissionCache();
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')
            ->where('name', 'roles.manage')
            ->where('guard_name', 'web')
            ->value('id');

        if ($permissionId === null) {
            return;
        }

        $unsafeAssignment = DB::table('model_has_permissions')->where('permission_id', $permissionId)->exists()
            || DB::table('role_has_permissions')
                ->join('roles', 'roles.id', '=', 'role_has_permissions.role_id')
                ->where('role_has_permissions.permission_id', $permissionId)
                ->where(fn ($query) => $query
                    ->where('roles.name', '!=', 'super-admin')
                    ->orWhere('roles.guard_name', '!=', 'web'))
                ->exists();

        throw_if($unsafeAssignment, 'Role management rollback would discard authorization state.');

        DB::table('permissions')->where('id', $permissionId)->delete();
        $this->clearPermissionCache();
    }

    private function clearPermissionCache(): void
    {
        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }
};
