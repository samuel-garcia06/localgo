<?php

return [
    'broadcasting' => [
        'echo' => [
            'broadcaster' => 'reverb',
            'key' => env('REVERB_APP_KEY'),
            'wsHost' => env('REVERB_HOST', '127.0.0.1'),
            'wsPort' => (int) env('REVERB_PORT', 8081),
            'wssPort' => (int) env('REVERB_PORT', 8081),
            'forceTLS' => env('REVERB_SCHEME', 'http') === 'https',
            'enabledTransports' => ['ws', 'wss'],
        ],
    ],
];
