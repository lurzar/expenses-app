<?php

namespace App\Modules\ActivityLog;

enum ActivityEvent: string
{
    case AccountDeleted = 'account.deleted';
    case AccountRegistered = 'account.registered';
    case AccountProfileUpdated = 'account.profile_updated';
    case PlanningCreated = 'planning.created';
    case PlanningDeleted = 'planning.deleted';

    public function subjectType(): string
    {
        return match ($this) {
            self::AccountDeleted,
            self::AccountProfileUpdated,
            self::AccountRegistered => 'account',
            self::PlanningCreated,
            self::PlanningDeleted => 'planning',
        };
    }
}
