<?php

namespace App\Modules\Planning\Permissions;

use App\Modules\Authorization\PermissionDefinition;
use App\Modules\Authorization\PermissionScope;

enum PlanningPermission: string implements PermissionDefinition
{
    case View = 'planning.view';
    case Create = 'planning.create';
    case Delete = 'planning.delete';

    public function module(): string
    {
        return 'planning';
    }

    public function labelKey(): string
    {
        return "authorization::permissions.planning.{$this->action()}.label";
    }

    public function descriptionKey(): string
    {
        return "authorization::permissions.planning.{$this->action()}.description";
    }

    public function scope(): PermissionScope
    {
        return PermissionScope::Account;
    }

    private function action(): string
    {
        return str($this->value)->after('.')->toString();
    }
}
