<?php

namespace App\Modules\Authorization\Services;

use App\Models\User;
use App\Modules\ActivityLog\ActivityEvent;
use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Authorization\RoleName;
use Illuminate\Support\Facades\DB;

final class RoleAssignmentService
{
    public function __construct(private readonly ActivityRecorder $activityRecorder) {}

    public function assign(User $actor, User $subject, RoleName $role): bool
    {
        return DB::transaction(function () use ($actor, $subject, $role): bool {
            $target = User::query()
                ->whereKey($subject->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($target->hasRole($role->value)) {
                return false;
            }

            $target->assignRole($role->value);
            $this->record(ActivityEvent::AuthorizationRoleAssigned, $actor, $target, $role);

            return true;
        });
    }

    public function remove(User $actor, User $subject, RoleName $role): bool
    {
        return DB::transaction(function () use ($actor, $subject, $role): bool {
            $target = User::query()
                ->whereKey($subject->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $target->hasRole($role->value)) {
                return false;
            }

            $target->removeRole($role->value);
            $this->record(ActivityEvent::AuthorizationRoleRemoved, $actor, $target, $role);

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
}
