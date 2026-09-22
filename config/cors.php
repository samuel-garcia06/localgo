<?php

return [
    'paths' => ['api/*', 'broadcasting/auth'],
    'allowed_methods' => ['GET', 'POST', 'PATCH'],
    'allowed_origins' => array_values(array_filter([
        rtrim((string) env('APP_URL', 'http://localhost'), '/'),
    ])),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Accept', 'Authorization', 'Origin'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
