<?php

namespace App\Modules\Authorization\Services;

use App\Models\User;
use App\Modules\ActivityLog\ActivityEvent;
use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Authorization\Exceptions\SuperAdminLifecycleException;
use App\Modules\Authorization\RoleName;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class SuperAdminLifecycleService
{
    /** @var array<string, true> */
    private array $authorizedActiveStatusMutations = [];

    public function __construct(
        private readonly ActivityRecorder $activityRecorder,
        private readonly PermissionRegistrar $permissionRegistrar,
    ) {}

    public function grant(User $subject, ?User $actor = null): bool
    {
        $this->ensureAuditIsAvailable();

        $changed = DB::transaction(function () use ($actor, $subject): bool {
            $role = $this->lockRole();
            $target = User::query()
                ->withTrashed()
                ->whereKey($subject->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureEligible($target);

            if ($target->hasRole($role)) {
                return false;
            }

            $target->assignRole($role);
            $this->revokeAuthorizationSessions($target);

            $this->activityRecorder->record(
                ActivityEvent::AuthorizationSuperAdminGranted,
                $actor?->user_id,
                'account',
                $target->user_id,
            );

            return true;
        });

        if ($changed) {
            $this->permissionRegistrar->forgetCachedPermissions();
        }

        return $changed;
    }

    public function rotate(User $previous, User $replacement, ?User $actor = null): void
    {
        $this->ensureAuditIsAvailable();

        DB::transaction(function () use ($actor, $previous, $replacement): void {
            $role = $this->lockRole();
            $current = $this->lockUser($previous);
            $target = $this->lockUser($replacement);

            if ($current->is($target)) {
                throw new SuperAdminLifecycleException('Super-admin rotation requires two different accounts.');
            }

            if (! $current->hasRole($role)) {
                throw new SuperAdminLifecycleException('The previous account does not have super-admin access.');
            }

            $this->ensureEligible($target);

            if (! $target->hasRole($role)) {
                $target->assignRole($role);
            }

            $current->removeRole($role);
            $this->revokeAuthorizationSessions($target);
            $this->revokeAuthorizationSessions($current);

            $this->activityRecorder->record(
                ActivityEvent::AuthorizationSuperAdminRotated,
                $actor?->user_id,
                'account',
                $target->user_id,
                ['previous_account_id' => $current->user_id],
            );
        });

        $this->permissionRegistrar->forgetCachedPermissions();
    }

    public function remove(User $subject, ?User $actor = null): bool
    {
        $this->ensureAuditIsAvailable();

        $changed = DB::transaction(function () use ($actor, $subject): bool {
            $role = $this->lockRole();
            $target = $this->lockUser($subject);

            if (! $target->hasRole($role)) {
                return false;
            }

            $this->ensureAnotherActiveOperatorRemains($target, $role);
            $target->removeRole($role);
            $this->revokeAuthorizationSessions($target);

            $this->activityRecorder->record(
                ActivityEvent::AuthorizationSuperAdminRemoved,
                $actor?->user_id,
                'account',
                $target->user_id,
            );

            return true;
        });

        if ($changed) {
            $this->permissionRegistrar->forgetCachedPermissions();
        }

        return $changed;
    }

    /**
     * @param  Closure(User): array<string, mixed>  $mutation
     */
    public function mutateActiveStatus(
        User $subject,
        ?User $actor,
        ActivityEvent $event,
        Closure $mutation,
    ): void {
        $this->ensureAuditIsAvailable();

        DB::transaction(function () use ($actor, $event, $mutation, $subject): void {
            $role = $this->lockRole();
            $target = $this->lockUser($subject);

            if ($target->hasRole($role)) {
                if ($this->isActive($target)) {
                    $this->ensureAnotherActiveOperatorRemains($target, $role);
                }

                $this->revokeAuthorizationSessions($target);
            }

            $key = (string) $target->getKey();
            $this->authorizedActiveStatusMutations[$key] = true;

            try {
                $metadata = $mutation($target);
            } finally {
                unset($this->authorizedActiveStatusMutations[$key]);
            }

            $this->activityRecorder->record(
                $event,
                $actor?->user_id,
                'account',
                $target->user_id,
                $metadata,
            );
        });
    }

    public function ensureCanLoseActiveStatus(User $subject): void
    {
        if (isset($this->authorizedActiveStatusMutations[(string) $subject->getKey()])) {
            return;
        }

        DB::transaction(function () use ($subject): void {
            $role = $this->lockRole();
            $target = $this->lockUser($subject);

            if (! $target->hasRole($role)) {
                return;
            }

            throw new SuperAdminLifecycleException(
                'Protected super-admin status changes must use the audited lifecycle.',
            );
        });
    }

    private function ensureAuditIsAvailable(): void
    {
        if (config('activity-log.enabled') !== true) {
            throw new SuperAdminLifecycleException('Super-admin changes require activity logging.');
        }
    }

    private function lockRole(): Role
    {
        $role = Role::query()
            ->where('name', RoleName::SuperAdmin->value)
            ->where('guard_name', 'web')
            ->lockForUpdate()
            ->first();

        if (! $role instanceof Role) {
            throw new SuperAdminLifecycleException('The authorization catalog is not synchronized.');
        }

        return $role;
    }

    private function lockUser(User $user): User
    {
        return User::query()
            ->withTrashed()
            ->whereKey($user->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function ensureEligible(User $user): void
    {
        if ($user->trashed() || $user->email_verified_at === null) {
            throw new SuperAdminLifecycleException('The target account is not eligible for super-admin access.');
        }
    }

    private function ensureAnotherActiveOperatorRemains(User $subject, Role $role): void
    {
        if (! $this->isActive($subject)) {
            return;
        }

        $activeOperators = User::query()
            ->whereNotNull('email_verified_at')
            ->whereHas('roles', fn (Builder $query): Builder => $query->whereKey($role->getKey()))
            ->count();

        if ($activeOperators <= 1) {
            throw new SuperAdminLifecycleException('The final active super-admin cannot be removed.');
        }
    }

    private function isActive(User $user): bool
    {
        return ! $user->trashed() && $user->email_verified_at !== null;
    }

    private function revokeAuthorizationSessions(User $user): void
    {
        $user->forceFill([
            'authorization_version' => $user->authorization_version + 1,
            'remember_token' => Str::random(60),
        ])->saveQuietly();
    }
}
