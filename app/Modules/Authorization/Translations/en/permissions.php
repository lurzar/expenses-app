<?php

return [
    'admin' => [
        'access' => [
            'label' => 'Access Admin',
            'description' => 'Enter the application administration area.',
        ],
    ],
    'roles' => [
        'manage' => [
            'label' => 'Manage roles',
            'description' => 'Create custom roles and map approved capabilities.',
        ],
    ],
    'users' => [
        'view' => [
            'label' => 'View users',
            'description' => 'View the minimized Admin user directory.',
        ],
        'manage-roles' => [
            'label' => 'Manage roles',
            'description' => 'Assign or remove approved administrative roles.',
        ],
        'manage-super-admin' => [
            'label' => 'Manage super-admins',
            'description' => 'Assign or remove protected super-admin access.',
        ],
    ],
    'planning' => [
        'view' => [
            'label' => 'View planning',
            'description' => 'View planning data owned by the account.',
        ],
        'create' => [
            'label' => 'Create planning',
            'description' => 'Create planning data for the account.',
        ],
        'delete' => [
            'label' => 'Delete planning',
            'description' => 'Delete planning data owned by the account.',
        ],
    ],
];
