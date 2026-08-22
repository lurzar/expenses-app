<?php

namespace App\Modules\Authorization\Services;

use App\Models\User;
use App\Modules\ActivityLog\ActivityEvent;
use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Authorization\Exceptions\RoleAssignmentException;
use App\Modules\Authorization\Permissions\SystemPermission;
use App\Modules\Authorization\RoleName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RoleAssignmentService
{
    public function __construct(
        private readonly ActivityRecorder $activityRecorder,
        private readonly SuperAdminLifecycleService $superAdminLifecycle,
        private readonly PermissionRegistrar $permissionRegistrar,
    ) {}

    public function assign(User $actor, User $subject, RoleName $role): bool
    {
        if ($role === RoleName::SuperAdmin) {
            return $this->superAdminLifecycle->grant($subject, $actor);
        }

        $this->ensureActivityIsAvailable();

        $changed = DB::transaction(function () use ($actor, $subject, $role): bool {
            $target = User::query()
                ->whereKey($subject->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($target->hasRole($role->value)) {
                return false;
            }

            $target->assignRole($role->value);
            $this->revokeAuthorizationSessions($target);
            $this->record(ActivityEvent::AuthorizationRoleAssigned, $actor, $target, $role);

            return true;
        });

        if ($changed) {
            $this->permissionRegistrar->forgetCachedPermissions();
        }

        return $changed;
    }

    public function remove(User $actor, User $subject, RoleName $role): bool
    {
        if ($role === RoleName::SuperAdmin) {
            return $this->superAdminLifecycle->remove($subject, $actor);
        }

        $this->ensureActivityIsAvailable();

        $changed = DB::transaction(function () use ($actor, $subject, $role): bool {
            $target = User::query()
                ->whereKey($subject->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $target->hasRole($role->value)) {
                return false;
            }

            $target->removeRole($role->value);
            $this->revokeAuthorizationSessions($target);
            $this->record(ActivityEvent::AuthorizationRoleRemoved, $actor, $target, $role);

            return true;
        });

        if ($changed) {
            $this->permissionRegistrar->forgetCachedPermissions();
        }

        return $changed;
    }

    /**
     * @param  list<RoleName>  $roles
     */
    public function syncAdministrativeRoles(
        User $actor,
        User $subject,
        array $roles,
        int $expectedVersion,
    ): bool {
        $this->ensureActivityIsAvailable();

        $desired = collect($roles)
            ->map(fn (RoleName $role): string => $role->value)
            ->unique()
            ->sort()
            ->values()
            ->all();
        $manageable = [RoleName::Admin->value, RoleName::SuperAdmin->value];

        if (array_diff($desired, $manageable) !== []) {
            throw new RoleAssignmentException('Only approved administrative roles can be managed.');
        }

        return DB::transaction(function () use ($actor, $desired, $expectedVersion, $manageable, $subject): bool {
            $this->lockProtectedRole();
            $lockedUsers = [];
            $userKeys = array_values(array_unique([$actor->getKey(), $subject->getKey()]));
            sort($userKeys);

            foreach ($userKeys as $userKey) {
                $lockedUsers[$userKey] = User::query()
                    ->whereKey($userKey)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $currentActor = $lockedUsers[$actor->getKey()];
            $target = $lockedUsers[$subject->getKey()];

            if (! $currentActor->can(SystemPermission::ManageUserRoles->value)) {
                throw new RoleAssignmentException('You are not allowed to manage administrative roles.');
            }

            $current = $target->roles
                ->pluck('name')
                ->intersect($manageable)
                ->sort()
                ->values()
                ->all();

            if ($desired === $current) {
                return false;
            }

            if ($target->authorization_version !== $expectedVersion) {
                throw new RoleAssignmentException(
                    'The account roles changed. Refresh and try again.',
                    'authorization_version',
                );
            }

            $added = array_values(array_diff($desired, $current));
            $removed = array_values(array_diff($current, $desired));

            if (in_array(RoleName::SuperAdmin->value, [...$added, ...$removed], true)
                && ! $currentActor->can(SystemPermission::ManageSuperAdmin->value)) {
                throw new RoleAssignmentException('You are not allowed to manage super-admin access.');
            }

            if ($currentActor->is($target) && $removed !== []) {
                throw new RoleAssignmentException('You cannot remove your own administrative access.');
            }

            if ($added !== [] && ($target->trashed() || $target->email_verified_at === null)) {
                throw new RoleAssignmentException('Administrative access requires an active verified account.');
            }

            foreach ($added as $role) {
                $this->assign($currentActor, $target, RoleName::from($role));
            }

            foreach ($removed as $role) {
                $this->remove($currentActor, $target, RoleName::from($role));
            }

            return true;
        });
    }

    private function record(ActivityEvent $event, User $actor, User $subject, RoleName $role): void
    {
        $this->activityRecorder->record(
            $event,
            $actor->user_id,
            'account',
            $subject->user_id,
            ['role' => $role->value],
        );
    }

    private function revokeAuthorizationSessions(User $user): void
    {
        $user->forceFill([
            'authorization_version' => $user->authorization_version + 1,
            'remember_token' => Str::random(60),
        ])->saveQuietly();
    }

    private function ensureActivityIsAvailable(): void
    {
        if (config('activity-log.enabled') !== true) {
            throw new RoleAssignmentException('Role changes require activity logging.');
        }
    }

    private function lockProtectedRole(): void
    {
        $role = Role::query()
            ->where('name', RoleName::SuperAdmin->value)
            ->where('guard_name', 'web')
            ->lockForUpdate()
            ->first();

        if (! $role instanceof Role) {
            throw new RoleAssignmentException('The authorization catalog is not synchronized.');
        }
    }
}
