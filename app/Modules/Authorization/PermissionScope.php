<?php

namespace App\Modules\Authorization;

enum PermissionScope: string
{
    case Account = 'account';
    case ControlPlane = 'control-plane';
}
