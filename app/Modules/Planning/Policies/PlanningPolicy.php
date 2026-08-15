<?php

namespace App\Modules\Planning\Policies;

use App\Models\User;
use App\Modules\Planning\Models\Planning;

class PlanningPolicy
{
    public function view(User $user, Planning $planning): bool
    {
        return $user->getKey() === $planning->user_id;
    }

    public function delete(User $user, Planning $planning): bool
    {
        return $user->getKey() === $planning->user_id;
    }
}
