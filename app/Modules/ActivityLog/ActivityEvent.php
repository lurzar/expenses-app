<?php

namespace App\Modules\ActivityLog;

enum ActivityEvent: string
{
    case AccountDeleted = 'account.deleted';
    case AccountRegistered = 'account.registered';
    case AccountProfileUpdated = 'account.profile_updated';
    case AuthorizationCatalogSynchronized = 'authorization.catalog_synchronized';
    case AuthorizationCustomRoleCreated = 'authorization.custom_role_created';
    case AuthorizationCustomRoleUpdated = 'authorization.custom_role_updated';
    case AuthorizationCustomRoleRetired = 'authorization.custom_role_retired';
    case AuthorizationRoleAssigned = 'authorization.role_assigned';
    case AuthorizationRoleRemoved = 'authorization.role_removed';
    case AuthorizationSuperAdminGranted = 'authorization.super_admin_granted';
    case AuthorizationSuperAdminRemoved = 'authorization.super_admin_removed';
    case AuthorizationSuperAdminRotated = 'authorization.super_admin_rotated';
    case PlanningCreated = 'planning.created';
    case PlanningDeleted = 'planning.deleted';

    public function subjectType(): string
    {
        return match ($this) {
            self::AccountDeleted,
            self::AccountProfileUpdated,
            self::AccountRegistered => 'account',
            self::AuthorizationCatalogSynchronized => 'authorization_catalog',
            self::AuthorizationCustomRoleCreated => 'authorization_role',
            self::AuthorizationCustomRoleUpdated => 'authorization_role',
            self::AuthorizationCustomRoleRetired => 'authorization_role',
            self::AuthorizationRoleAssigned,
            self::AuthorizationRoleRemoved,
            self::AuthorizationSuperAdminGranted,
            self::AuthorizationSuperAdminRemoved,
            self::AuthorizationSuperAdminRotated => 'account',
            self::PlanningCreated,
            self::PlanningDeleted => 'planning',
        };
    }
}
