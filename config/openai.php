<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key
    |--------------------------------------------------------------------------
    |
    | Your OpenAI API key. This MUST be set in your .env file.
    |
    */

    'api_key' => env('OPENAI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Organization (Optional)
    |--------------------------------------------------------------------------
    |
    | Only needed if your account explicitly uses an organization ID.
    |
    */

    'organization' => env('OPENAI_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Project (Optional)
    |--------------------------------------------------------------------------
    |
    | Used only for legacy keys that require project association.
    |
    */

    'project' => env('OPENAI_PROJECT'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Base URL
    |--------------------------------------------------------------------------
    |
    | Default: https://api.openai.com/v1
    | Change only if using a proxy or custom endpoint.
    |
    */

    'base_uri' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout (seconds)
    |--------------------------------------------------------------------------
    */

    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | SSL Verification
    |--------------------------------------------------------------------------
    |
    | Disable ONLY for local development if SSL errors occur.
    | NEVER disable in production.
    |
    */

    'verify' => env('OPENAI_SSL_VERIFY', true),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Options
    |--------------------------------------------------------------------------
    */

    'http_options' => [
        'verify' => env('OPENAI_SSL_VERIFY', true),
    ],
];
