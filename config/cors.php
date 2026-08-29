<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Whitelist the Nuxt dev server plus the configured Laravel app URL.
    // Production should set FRONTEND_URL in .env to the deployed Nuxt origin.
    'allowed_origins' => array_values(array_filter([
        env('FRONTEND_URL'),
        env('FRONTEND_URL_RTL'),
        'https://cv.test',
        'http://cv.test',
        'http://localhost:3000',
        'http://127.0.0.1:3000',
    ])),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Required so the httpOnly auth cookie can travel cross-origin
    // (Nuxt :3000 → Laravel :8000 in dev).
    'supports_credentials' => true,

];
