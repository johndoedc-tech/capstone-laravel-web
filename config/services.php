<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],
    'ml_api' => [
        'url' => env('ML_API_URL', 'http://127.0.0.1:5000'),
    ],
    'weather' => [
        'base_url' => env('WEATHER_API_BASE_URL', 'https://weather.googleapis.com/v1'),
        'api_key' => env('WEATHER_API_KEY'),
        'timeout' => (int) env('WEATHER_API_TIMEOUT', 10),
        'retries' => (int) env('WEATHER_API_RETRIES', 1),
    ],
    'gemini' => [
        'base_url' => env('GEMINI_API_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'fallback_models' => env('GEMINI_FALLBACK_MODELS', ''),
        'timeout' => (int) env('GEMINI_API_TIMEOUT', 20),
        'retries' => (int) env('GEMINI_API_RETRIES', 0),
        'allow_http_retry' => (bool) env('GEMINI_ALLOW_HTTP_RETRY', false),
        'model_quota_cooldown_seconds' => (int) env('GEMINI_MODEL_QUOTA_COOLDOWN_SECONDS', 75),
        'max_output_tokens' => (int) env('GEMINI_MAX_OUTPUT_TOKENS', 480),
        'detailed_max_output_tokens' => (int) env('GEMINI_DETAILED_MAX_OUTPUT_TOKENS', 1024),
    ],
];
