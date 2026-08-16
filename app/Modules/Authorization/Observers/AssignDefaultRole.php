<?php

namespace App\Modules\Authorization\Observers;

use App\Models\User;
use App\Modules\Authorization\RoleName;

class AssignDefaultRole
{
    public function created(User $user): void
    {
        $user->assignRole(RoleName::User->value);
    }
}
