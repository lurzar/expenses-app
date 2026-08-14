<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
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
    }
}
