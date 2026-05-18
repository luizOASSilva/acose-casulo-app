<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'auth/*',
        'login',
        'logout',
        'donations',
        'donations/*',
        'webhook/*',
        'dashboard',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://luizoassilva.xyz',
        'https://api.luizoassilva.xyz',
        'https://acose-casulo-58kv5ok32-luizoassilvas-projects.vercel.app',
        'https://acose-casulo-web-luizoassilvas-projects.vercel.app/'
    ],

    'allowed_origins_patterns' => [
        '#^https://.*\.vercel\.app$#',
    ],

    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
