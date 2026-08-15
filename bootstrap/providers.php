<?php

use App\Modules\ActivityLog\ActivityLogServiceProvider;
use App\Modules\Auth\AuthServiceProvider;
use App\Modules\Dashboard\DashboardServiceProvider;
use App\Modules\Expenses\ExpensesServiceProvider;
use App\Modules\Landing\LandingServiceProvider;
use App\Modules\Language\LanguageServiceProvider;
use App\Modules\Planning\PlanningServiceProvider;
use App\Modules\Profile\ProfileServiceProvider;
use App\Providers\AppServiceProvider;

return [
    // Core Service Providers
    AppServiceProvider::class,

    // Module Service Providers
    ActivityLogServiceProvider::class,
    AuthServiceProvider::class,
    LandingServiceProvider::class,
    PlanningServiceProvider::class,
    ExpensesServiceProvider::class,
    DashboardServiceProvider::class,
    ProfileServiceProvider::class,
    LanguageServiceProvider::class,
];
