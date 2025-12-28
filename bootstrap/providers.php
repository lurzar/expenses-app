<?php

return [
    App\Core\Providers\AppServiceProvider::class,
    App\Core\Providers\TelescopeServiceProvider::class,

    // Module Service Providers
    App\Modules\Auth\AuthServiceProvider::class,
    App\Modules\Landing\LandingServiceProvider::class,
    App\Modules\Planning\PlanningServiceProvider::class,
    App\Modules\Expenses\ExpensesServiceProvider::class,
    App\Modules\Dashboard\DashboardServiceProvider::class,
    App\Modules\Profile\ProfileServiceProvider::class,
    App\Modules\Language\LanguageServiceProvider::class,
];
