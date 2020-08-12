<?php

return [
    // Enable or disable job logging generally
    'jobs'         => env('LOG_JOBS', false),
    // Enable or disable logging all failed attempts instead of only the last one.
    // All attempts will have different report IDs but the same job ID.
    'job_attempts' => env('LOG_JOB_ATTEMPTS', false),
    // Log all requests that use the `log` middleware
    'requests'     => env('LOG_REQUESTS', false),

    // Elasticsearch config
    'elasticsearch' => [
        'hosts' => [
            env('LOG_ELASTICSEARCH_HOST'),
        ],
        'prefix' => config('app.env').'_',

        //Log retention number of days (required artisan laraveleslogs:tidy command to run daily)
        'keep_days' => env('LOG_ELASTICSEARCH_KEEP_DAYS'),
    ],

    // Keys used to catch elements in logging array keys - will be replaced with redaction ink
    // and not displayed in logs.
    'redaction' => [
        'password',
        'password_confirmation',
        'authorization',
        'telephone_number',
        'email',
        'algolia_key',
        'access-token'
    ],

    // The ink used to redact sensitive keys in logs.
    'redaction_ink' => config('LOG_REDACTION_INK', '[--REDACTED--]'),
];
