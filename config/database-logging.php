<?php

return [
    'enable_logging' => env('ENABLE_LOGGING', true),
    'middleware' => [
        'web',
        'auth'
    ],
    'model' => [
        'App\Models\User' => 'name' // Name user
    ],
    'log_events' => [
        'create'     => true,
        'update'     => true,
        'delete'     => true,
    ],
    'route_path' => 'database-logging',
    'assets_path' => 'assets/database-logging',
    'duration' => 10, // days
];
