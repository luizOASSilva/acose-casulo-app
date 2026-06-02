<?php

return [
    'property_id' => env('GOOGLE_ANALYTICS_PROPERTY_ID'),

    'auth_mode' => env('GOOGLE_ANALYTICS_AUTH_MODE', 'service_account'),

    'credentials_path' => env(
        'GOOGLE_ANALYTICS_CREDENTIALS_PATH',
        'storage/app/private/analytics/service-account-credentials.json'
    ),

    'client_id' => env('GOOGLE_ANALYTICS_CLIENT_ID'),

    'client_secret' => env('GOOGLE_ANALYTICS_CLIENT_SECRET'),

    'refresh_token' => env('GOOGLE_ANALYTICS_REFRESH_TOKEN'),

    'cache_seconds' => (int) env('GOOGLE_ANALYTICS_CACHE_SECONDS', 600),

    'excluded_path_prefixes' => array_values(array_filter([
        '/admin',
        env('PANEL_SLUG') ? '/acesso/' . env('PANEL_SLUG') : null,
    ])),
];
