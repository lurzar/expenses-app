<?php

namespace App\Observers;

use App\Models\User;
use App\Modules\Planning\Services\PlanningCache;

class UserObserver
{
    public function __construct(
        private readonly PlanningCache $planningCache,
    ) {}

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
