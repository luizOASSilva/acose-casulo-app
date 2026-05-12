return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://acose-casulo-web.vercel.app',
    ],

    'allowed_headers' => ['*'],

    'supports_credentials' => true,
];
