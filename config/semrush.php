<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Semrush API Key
    |--------------------------------------------------------------------------
    |
    | The API key sent as the `key` query parameter on every Analytics API
    | request. Grab yours at https://www.semrush.com/api-analytics/.
    |
    | When this is null the client falls back to config('services.semrush.key').
    |
    */
    'key' => env('SEMRUSH_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Database
    |--------------------------------------------------------------------------
    |
    | The regional database used by keyword and organic reports when no
    | `$database` argument is passed, e.g. `us`, `br`, `uk`, `de`.
    |
    */
    'database' => env('SEMRUSH_DATABASE', 'us'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL every report is resolved against.
    |
    */
    'base_url' => env('SEMRUSH_BASE_URL', 'https://api.semrush.com'),
];
