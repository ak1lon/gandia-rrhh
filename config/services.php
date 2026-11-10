<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'didit' => [
        'app_id' => env('DIDIT_APP_ID'),
        'workflow_id' => env('DIDIT_WORKFLOW_ID'),
        'api_key' => env('DIDIT_API_KEY'),
        'base_url' => env('DIDIT_BASE_URL', 'https://verification.didit.me/v3'),
        'timeout' => (int) env('DIDIT_TIMEOUT', 30),
        'connect_timeout' => (int) env('DIDIT_CONNECT_TIMEOUT', 10),
        'rate_limit_per_hour' => (int) env('DIDIT_RATE_LIMIT_PER_HOUR', 5),
        'quality_thresholds' => [
            'focus' => (float) env('DIDIT_THRESHOLD_FOCUS', 60),
            'brightness' => (float) env('DIDIT_THRESHOLD_BRIGHTNESS', 50),
            'resolution' => (float) env('DIDIT_THRESHOLD_RESOLUTION', 60),
            'overall' => (float) env('DIDIT_THRESHOLD_OVERALL', 60),
        ],
    ],

];
