<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Only include routes matching these patterns
    |--------------------------------------------------------------------------
    |
    | Routes matching these patterns will be included in Ziggy's output.
    | Use '*' as a wildcard. Important: Do not define BOTH 'only' and 'except'.
    |
    */
    'only' => [
        'landing',
        'dashboard',
        'login',
        'register',
        'logout',
        'password.*',
        'verification.*',
        'profile.*',
        'planning.*',
        'expenses.*',
        'admin.*',
        'language',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Groups
    |--------------------------------------------------------------------------
    |
    | If you want to define groups of routes that can be loaded separately,
    | you can define them here and reference them using @routes('group').
    |
    */
    'groups' => [
        // 'admin' => ['admin.*'],
    ],
];
