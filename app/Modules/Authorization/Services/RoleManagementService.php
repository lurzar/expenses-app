<?php

namespace App\Modules\Authorization\Services;

use App\Models\User;
use App\Modules\ActivityLog\ActivityEvent;
use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Authorization\Exceptions\RoleManagementException;
use App\Modules\Authorization\PermissionCatalog;
use App\Modules\Authorization\Permissions\SystemPermission;
use App\Modules\Authorization\RoleName;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RoleManagementService
{
    public function __construct(
        private readonly ActivityRecorder $activityRecorder,
        private readonly PermissionCatalog $catalog,
        private readonly PermissionRegistrar $permissionRegistrar,
    ) {}

    /** @param list<string> $permissions */
    public function create(User $actor, string $name, array $permissions): Role
    {
        $this->ensureActorCanManageRoles($actor);
        $this->ensureActivityIsAvailable();
        $normalizedName = $this->normalizeName($name);
        $permissionNames = $this->validatedPermissionNames($permissions);

        $role = DB::transaction(function () use ($actor, $normalizedName, $permissionNames): Role {
            if (RoleName::tryFrom($normalizedName) !== null || Role::query()
                ->where('name', $normalizedName)
                ->where('guard_name', 'web')
                ->exists()) {
                throw new RoleManagementException('That role name is unavailable.');
            }

            $role = Role::query()->create(['name' => $normalizedName, 'guard_name' => 'web']);
            $role->syncPermissions($permissionNames);

            $this->activityRecorder->record(
                ActivityEvent::AuthorizationCustomRoleCreated,
                $actor->user_id,
                'authorization_role',
                null,
                ['role' => $role->name, 'permissions' => $permissionNames],
            );

            return $role;
        });

        $this->permissionRegistrar->forgetCachedPermissions();

        return $role;
    }

    /** @param list<string> $permissions */
    public function update(User $actor, string $currentName, string $name, array $permissions, string $expectedUpdatedAt): void
    {
        $this->ensureActorCanManageRoles($actor);
        $this->ensureActivityIsAvailable();
        $normalizedName = $this->normalizeName($name);
        $permissionNames = $this->validatedPermissionNames($permissions);

        DB::transaction(function () use ($actor, $currentName, $expectedUpdatedAt, $normalizedName, $permissionNames): void {
            $role = Role::query()
                ->where('name', $currentName)
                ->where('guard_name', 'web')
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureCustomRole($role);

            if ($role->updated_at?->toISOString() !== CarbonImmutable::parse($expectedUpdatedAt)->toISOString()) {
                throw new RoleManagementException('The role changed. Refresh and try again.', 'updated_at');
            }

            if ($normalizedName !== $role->name && Role::query()
                ->where('name', $normalizedName)
                ->where('guard_name', 'web')
                ->exists()) {
                throw new RoleManagementException('That role name is unavailable.');
            }

            $currentPermissions = $role->permissions->pluck('name')->sort()->values()->all();

            if (array_diff($currentPermissions, $this->catalog->names()) !== []) {
                throw new RoleManagementException('Unknown role permissions require operator review.', 'permissions');
            }

            if ($normalizedName === $role->name && $permissionNames === $currentPermissions) {
                return;
            }

            $previousName = $role->name;
            $role->name = $normalizedName;
            $role->save();
            $role->syncPermissions($permissionNames);
            $role->touch();
            $this->revokeAssignedUserSessions($role);

            $this->activityRecorder->record(
                ActivityEvent::AuthorizationCustomRoleUpdated,
                $actor->user_id,
                'authorization_role',
                null,
                [
                    'role' => $role->name,
                    'previous_role' => $previousName,
                    'permissions' => $permissionNames,
                    'previous_permissions' => $currentPermissions,
                ],
            );
        });

        $this->permissionRegistrar->forgetCachedPermissions();
    }

    public function retire(User $actor, string $name): void
    {
        $this->ensureActorCanManageRoles($actor);
        $this->ensureActivityIsAvailable();

        DB::transaction(function () use ($actor, $name): void {
            $role = Role::query()
                ->where('name', $name)
                ->where('guard_name', 'web')
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureCustomRole($role);

            if (User::query()->whereHas('roles', fn ($query) => $query->whereKey($role->getKey()))->exists()) {
                throw new RoleManagementException('Assigned roles cannot be retired until every assignment is resolved.', 'roles');
            }

            $permissions = $role->permissions->pluck('name')->sort()->values()->all();

            if (array_diff($permissions, $this->catalog->names()) !== []) {
                throw new RoleManagementException('Unknown role permissions require operator review.', 'permissions');
            }

            $role->delete();

            $this->activityRecorder->record(
                ActivityEvent::AuthorizationCustomRoleRetired,
                $actor->user_id,
                'authorization_role',
                null,
                ['role' => $name, 'permissions' => $permissions],
            );
        });

        $this->permissionRegistrar->forgetCachedPermissions();
    }

    private function ensureActorCanManageRoles(User $actor): void
    {
        if (! $actor->can(SystemPermission::ManageRoles->value)) {
            throw new RoleManagementException('You are not allowed to manage roles.', 'roles');
        }
    }

    private function ensureActivityIsAvailable(): void
    {
        if (config('activity-log.enabled') !== true) {
            throw new RoleManagementException('Role changes require activity logging.', 'roles');
        }
    }

    private function normalizeName(string $name): string
    {
        $normalized = Str::of($name)
            ->trim()
            ->lower()
            ->replaceMatches('/[ _]+/', '-')
            ->replaceMatches('/-+/', '-')
            ->trim('-')
            ->toString();

        if (preg_match('/^[a-z][a-z0-9-]{2,49}$/', $normalized) !== 1) {
            throw new RoleManagementException('Role names must contain 3–50 letters, numbers, or hyphens.');
        }

        return $normalized;
    }

    /**
     * @param  list<string>  $permissions
     * @return list<string>
     */
    private function validatedPermissionNames(array $permissions): array
    {
        $names = array_values(array_unique($permissions));
        sort($names);

        if (array_diff($names, $this->catalog->names()) !== []) {
            throw new RoleManagementException('Role permissions must come from the approved catalog.', 'permissions');
        }

        return $names;
    }

    private function ensureCustomRole(Role $role): void
    {
        if (RoleName::tryFrom($role->name) !== null) {
            throw new RoleManagementException('Protected roles are managed by code and cannot be changed.', 'name');
        }
    }

    private function revokeAssignedUserSessions(Role $role): void
    {
        User::query()
            ->whereHas('roles', fn ($query) => $query->whereKey($role->getKey()))
            ->lockForUpdate()
            ->each(function (User $user): void {
                $user->forceFill([
                    'authorization_version' => $user->authorization_version + 1,
                    'remember_token' => Str::random(60),
                ])->saveQuietly();
            });
    }
}
