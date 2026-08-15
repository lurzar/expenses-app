<?php

return [
    'enabled' => filter_var(
        env('ACTIVITY_LOG_ENABLED', true),
        FILTER_VALIDATE_BOOLEAN,
        FILTER_NULL_ON_FAILURE,
    ) ?? false,

    'retention_days' => filter_var(
        env('ACTIVITY_LOG_RETENTION_DAYS', 365),
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]],
    ) ?: 365,
];
