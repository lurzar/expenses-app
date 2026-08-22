<?php

namespace App\Modules\Planning\Policies;

use App\Models\User;
use App\Modules\Planning\Models\Planning;
use App\Modules\Planning\Permissions\PlanningPermission;

class PlanningPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PlanningPermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PlanningPermission::Create->value);
    }

    public function view(User $user, Planning $planning): bool
    {
        return $user->can(PlanningPermission::View->value)
            && $user->getKey() === $planning->user_id;
    }

    public function delete(User $user, Planning $planning): bool
    {
        return $user->can(PlanningPermission::Delete->value)
            && $user->getKey() === $planning->user_id;
    }
}
