<?php

return [

    'paths' => [
        'sanctum/csrf-cookie',
        'auth/*',
        'login',
        'logout',
        'donations',
        'donations/*',
        'webhook/*',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://luizoassilva.xyz',
        'https://acose-casulo-58kv5ok32-luizoassilvas-projects.vercel.app',
        'https://*.vercel.app',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
