<?php

namespace App\Modules\Authorization\Permissions;

use App\Modules\Authorization\PermissionDefinition;
use App\Modules\Authorization\PermissionScope;

enum SystemPermission: string implements PermissionDefinition
{
    case AccessAdmin = 'admin.access';
    case ManageRoles = 'roles.manage';
    case ManageUserRoles = 'users.manage-roles';
    case ManageSuperAdmin = 'users.manage-super-admin';
    case ViewUsers = 'users.view';

    public function module(): string
    {
        return match ($this) {
            self::AccessAdmin => 'admin',
            self::ManageRoles => 'roles',
            self::ManageUserRoles, self::ManageSuperAdmin, self::ViewUsers => 'users',
        };
    }

    public function labelKey(): string
    {
        return 'authorization::permissions.'.$this->value.'.label';
    }

    public function descriptionKey(): string
    {
        return 'authorization::permissions.'.$this->value.'.description';
    }

    public function scope(): PermissionScope
    {
        return PermissionScope::ControlPlane;
    }
}
