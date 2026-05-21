<?php

return [

    'paths' => [
        '*',
    ],

    'allowed_methods' => [
        '*',
    ],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost:3001',
        'http://127.0.0.1:3001',

        'https://luizoassilva.xyz',
        'https://www.luizoassilva.xyz',
        'https://api.luizoassilva.xyz',

        'https://acose-casulo-58kv5ok32-luizoassilvas-projects.vercel.app',
        'https://acose-casulo-web-luizoassilvas-projects.vercel.app',
    ],

    'allowed_origins_patterns' => [
        '#^https://.*\.vercel\.app$#',
    ],

    'allowed_headers' => [
        '*',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];

