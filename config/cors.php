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
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
