<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LiteLLM Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your LiteLLM proxy instance.
    |
    */

    'base_url' => env('LITELLM_BASE_URL', 'http://localhost:4000/v1'),

    /*
    |--------------------------------------------------------------------------
    | LiteLLM Master Key
    |--------------------------------------------------------------------------
    |
    | The master key for accessing LiteLLM Admin API endpoints.
    |
    */

    'master_key' => env('LITELLM_MASTER_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for LiteLLM API requests.
    |
    */

    'timeout' => env('LITELLM_TIMEOUT', 30),
];

