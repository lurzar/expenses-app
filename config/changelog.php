<?php

return [
    'releases' => [
        [
            'version' => '2.0.7',
            'status' => 'Released',
            'released_at' => '2026-08-15',
            'changes' => [
                [
                    'category' => 'Added',
                    'description' => 'Added clearer project documentation and operational guidance.',
                ],
                [
                    'category' => 'Changed',
                    'description' => 'Improved error diagnostics and automatic retention cleanup.',
                ],
                [
                    'category' => 'Security',
                    'description' => 'Restricted diagnostic access and strengthened sensitive-data redaction.',
                ],
            ],
        ],
        [
            'version' => '2.0.6',
            'status' => 'Released',
            'released_at' => '2026-08-15',
            'changes' => [
                [
                    'category' => 'Changed',
                    'description' => 'Restored ID-based links for planning and expense pages.',
                ],
                [
                    'category' => 'Security',
                    'description' => 'Updated application dependencies and pinned local service versions.',
                ],
                [
                    'category' => 'Added',
                    'description' => 'Added a public changes summary to the login page.',
                ],
            ],
        ],
    ],
];
