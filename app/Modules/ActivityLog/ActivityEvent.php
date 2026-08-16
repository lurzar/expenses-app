<?php

namespace App\Modules\ActivityLog;

enum ActivityEvent: string
{
    case AccountDeleted = 'account.deleted';
    case AccountRegistered = 'account.registered';
    case AccountProfileUpdated = 'account.profile_updated';
    case AuthorizationCatalogSynchronized = 'authorization.catalog_synchronized';
    case AuthorizationRoleAssigned = 'authorization.role_assigned';
    case AuthorizationRoleRemoved = 'authorization.role_removed';
    case PlanningCreated = 'planning.created';
    case PlanningDeleted = 'planning.deleted';

    public function subjectType(): string
    {
        return match ($this) {
            self::AccountDeleted,
            self::AccountProfileUpdated,
            self::AccountRegistered => 'account',
            self::AuthorizationCatalogSynchronized => 'authorization_catalog',
            self::AuthorizationRoleAssigned,
            self::AuthorizationRoleRemoved => 'account',
            self::PlanningCreated,
            self::PlanningDeleted => 'planning',
        };
    }
}
