<?php

return [
    'fib' => [
        'base_url' => env('FIB_BASE_URL', 'https://fib.stage.fib.iq'),
        'auth_url' => env('FIB_AUTH_URL', 'https://fib.stage.fib.iq/auth/realms/fib-online-shop/protocol/openid-connect/token'),
        'identifier' => env('FIB_IDENTIFIER', 'salahadin-testig-creds'),
        'secret_key' => env('FIB_SECRET_KEY', '9bcffb11-84d1-469b-b1c1-5cc765598720'),
    ],
];

