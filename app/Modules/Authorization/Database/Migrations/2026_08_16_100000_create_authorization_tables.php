<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        throw_if(empty($tableNames), 'Authorization package configuration is unavailable.');
        throw_if(config('permission.teams'), 'Team-scoped authorization is not supported.');

        Schema::create($tableNames['permissions'], function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission): void {
            $table->unsignedBigInteger($pivotPermission);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->cascadeOnDelete();
            $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole): void {
            $table->unsignedBigInteger($pivotRole);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->cascadeOnDelete();
            $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission): void {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);
            $table->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->cascadeOnDelete();
            $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->cascadeOnDelete();
            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        $this->installInitialCatalog($tableNames, $columnNames, $pivotRole, $pivotPermission);
        $this->clearPermissionCache();
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        $this->ensureRollbackIsSafe($tableNames);

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);

        $this->clearPermissionCache();
    }

    /**
     * Install the immutable v2.2.0 bootstrap snapshot. Ongoing changes use the catalog synchronizer.
     *
     * @param  array<string, string>  $tableNames
     * @param  array<string, string>  $columnNames
     */
    private function installInitialCatalog(array $tableNames, array $columnNames, string $pivotRole, string $pivotPermission): void
    {
        $now = now();
        $permissionNames = ['admin.access', 'planning.create', 'planning.delete', 'planning.view'];
        $roleNames = ['admin', 'super-admin', 'user'];

        DB::table($tableNames['permissions'])->insert(array_map(
            fn (string $name): array => ['name' => $name, 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now],
            $permissionNames,
        ));
        DB::table($tableNames['roles'])->insert(array_map(
            fn (string $name): array => ['name' => $name, 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now],
            $roleNames,
        ));

        $permissionIds = DB::table($tableNames['permissions'])->pluck('id', 'name');
        $roleIds = DB::table($tableNames['roles'])->pluck('id', 'name');
        $rolePermissions = [
            'user' => ['planning.create', 'planning.delete', 'planning.view'],
            'admin' => ['admin.access'],
            'super-admin' => ['admin.access'],
        ];

        foreach ($rolePermissions as $role => $permissions) {
            foreach ($permissions as $permission) {
                DB::table($tableNames['role_has_permissions'])->insert([
                    $pivotRole => $roleIds[$role],
                    $pivotPermission => $permissionIds[$permission],
                ]);
            }
        }

        DB::table('users')->orderBy('id')->chunkById(500, function ($users) use ($tableNames, $columnNames, $pivotRole, $roleIds): void {
            DB::table($tableNames['model_has_roles'])->insertOrIgnore($users->map(fn ($user): array => [
                $pivotRole => $roleIds['user'],
                'model_type' => 'App\\Models\\User',
                $columnNames['model_morph_key'] => $user->id,
            ])->all());
        });
    }

    private function clearPermissionCache(): void
    {
        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /** @param array<string, string> $tableNames */
    private function ensureRollbackIsSafe(array $tableNames): void
    {
        $knownPermissions = ['admin.access', 'planning.create', 'planning.delete', 'planning.view'];
        $knownRoles = ['admin', 'super-admin', 'user'];
        $expectedMappings = [
            ['admin', 'admin.access'],
            ['super-admin', 'admin.access'],
            ['user', 'planning.create'],
            ['user', 'planning.delete'],
            ['user', 'planning.view'],
        ];

        $unknownPermissionExists = DB::table($tableNames['permissions'])->whereNotIn('name', $knownPermissions)->exists()
            || DB::table($tableNames['permissions'])->where('guard_name', '!=', 'web')->exists();
        $unknownRoleExists = DB::table($tableNames['roles'])->whereNotIn('name', $knownRoles)->exists()
            || DB::table($tableNames['roles'])->where('guard_name', '!=', 'web')->exists();
        $directPermissionExists = DB::table($tableNames['model_has_permissions'])->exists();
        $nonDefaultRoleAssignmentExists = DB::table($tableNames['model_has_roles'])
            ->join($tableNames['roles'], $tableNames['roles'].'.id', '=', $tableNames['model_has_roles'].'.role_id')
            ->where($tableNames['roles'].'.name', '!=', 'user')
            ->orWhere($tableNames['roles'].'.guard_name', '!=', 'web')
            ->orWhere($tableNames['model_has_roles'].'.model_type', '!=', 'App\\Models\\User')
            ->exists();
        $orphanAssignmentExists = DB::table($tableNames['model_has_roles'])
            ->leftJoin('users', 'users.id', '=', $tableNames['model_has_roles'].'.model_id')
            ->whereNull('users.id')
            ->exists();
        $knownMappingCount = 0;

        foreach ($expectedMappings as [$role, $permission]) {
            if (DB::table($tableNames['role_has_permissions'])
                ->join($tableNames['roles'], $tableNames['roles'].'.id', '=', $tableNames['role_has_permissions'].'.role_id')
                ->join($tableNames['permissions'], $tableNames['permissions'].'.id', '=', $tableNames['role_has_permissions'].'.permission_id')
                ->where($tableNames['roles'].'.name', $role)
                ->where($tableNames['roles'].'.guard_name', 'web')
                ->where($tableNames['permissions'].'.name', $permission)
                ->where($tableNames['permissions'].'.guard_name', 'web')
                ->exists()) {
                $knownMappingCount++;
            }
        }

        $unexpectedMappingExists = DB::table($tableNames['role_has_permissions'])->count() > $knownMappingCount;

        if ($unknownPermissionExists || $unknownRoleExists || $directPermissionExists || $nonDefaultRoleAssignmentExists || $orphanAssignmentExists || $unexpectedMappingExists) {
            throw new RuntimeException('Authorization rollback would discard non-default state. Back up and explicitly resolve assignments before retrying.');
        }
    }
};
