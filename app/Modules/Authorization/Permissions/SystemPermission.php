<?php

namespace App\Modules\Authorization\Permissions;

use App\Modules\Authorization\PermissionDefinition;
use App\Modules\Authorization\PermissionScope;

enum SystemPermission: string implements PermissionDefinition
{
    case AccessAdmin = 'admin.access';

    public function module(): string
    {
        return 'admin';
    }

    public function labelKey(): string
    {
        return 'authorization::permissions.admin.access.label';
    }

    public function descriptionKey(): string
    {
        return 'authorization::permissions.admin.access.description';
    }

    public function scope(): PermissionScope
    {
        return PermissionScope::ControlPlane;
    }
}
