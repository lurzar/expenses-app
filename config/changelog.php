<?php

return [
    'releases' => [
        [
            'version' => '2.0.9',
            'status' => 'Released',
            'released_at' => '2026-08-15',
            'changes' => [
                [
                    'category' => 'Added',
                    'description' => 'Added faster access to monthly planning summaries.',
                ],
                [
                    'category' => 'Added',
                    'description' => 'Added clearer development, release, and cache operations guidance.',
                ],
                [
                    'category' => 'Security',
                    'description' => 'Isolated cached planning summaries by account and refreshed them after changes.',
                ],
            ],
        ],
        [
            'version' => '2.0.8',
            'status' => 'Released',
            'released_at' => '2026-08-15',
            'changes' => [
                [
                    'category' => 'Added',
                    'description' => 'Added durable activity history for key account and planning changes.',
                ],
                [
                    'category' => 'Changed',
                    'description' => 'Added automatic daily cleanup with configurable 365-day retention.',
                ],
                [
                    'category' => 'Security',
                    'description' => 'Limited activity records to public identifiers and allowlisted metadata.',
                ],
            ],
        ],
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
