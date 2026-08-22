<?php

namespace App\Observers;

use App\Models\User;
use App\Modules\Authorization\Services\SuperAdminLifecycleService;
use App\Modules\Planning\Services\PlanningCache;

class UserObserver
{
    public function __construct(
        private readonly PlanningCache $planningCache,
        private readonly SuperAdminLifecycleService $superAdminLifecycle,
    ) {}

    public function updating(User $user): void
    {
        if ($user->isDirty('email_verified_at')
            && $user->getOriginal('email_verified_at') !== null
            && $user->email_verified_at === null) {
            $this->superAdminLifecycle->ensureCanLoseActiveStatus($user);
        }
    }

    public function deleting(User $user): void
    {
        $this->superAdminLifecycle->ensureCanLoseActiveStatus($user);
    }

    /**
     * Handle the User "deleted" event.
     * Cascade soft-delete to related plannings.
     */
    public function deleted(User $user): void
    {
        $user->plannings()->delete();
    }

    /**
     * Handle the User "restored" event.
     * Restore related plannings when user is restored.
     */
    public function restored(User $user): void
    {
        $user->plannings()->withTrashed()->restore();
        $this->planningCache->forgetIndex((int) $user->getKey());
    }
}
