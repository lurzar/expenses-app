<?php

namespace App\Modules\Authorization;

use App\Modules\Authorization\Permissions\SystemPermission;
use App\Modules\Planning\Permissions\PlanningPermission;

enum RoleName: string
{
    case User = 'user';
    case Admin = 'admin';
    case SuperAdmin = 'super-admin';

    /** @return list<string> */
    public function permissions(): array
    {
        return match ($this) {
            self::User => [
                PlanningPermission::Create->value,
                PlanningPermission::Delete->value,
                PlanningPermission::View->value,
            ],
            self::Admin, self::SuperAdmin => [SystemPermission::AccessAdmin->value],
        };
    }
}
