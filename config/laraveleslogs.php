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

        // Auth type - (url | apikey) - url can include user/pass - eg 'http://user:pass@localhost:9200'
        'auth_type' => env('LOG_ELASTICSEARCH_AUTH_TYPE', 'url'),
        'auth_api_id' => env('LOG_ELASTICSEARCH_AUTH_API_ID', null),
        'auth_api_key' => env('LOG_ELASTICSEARCH_AUTH_API_KEY', null),

        // Prefix of index pattern.
        'prefix' => config('app.env').'_',

        //Log retention number of days (required artisan laraveleslogs:tidy command to run daily)
        'keep_days' => env('LOG_ELASTICSEARCH_KEEP_DAYS'),
    ],

    // Log group - default as app env, but can be set to be specific in the logs.
    'log_group' => env('LOG_GROUP', config('app.env')),

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

    // size of request and response logging (characters) - larger will be truncated in logs.
    'max_request' => env('LOG_REQUEST_MAX_SIZE', 200000),
    'max_response' => env('LOG_RESPONSE_MAX_SIZE', 200000),
];
