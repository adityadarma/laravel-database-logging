<?php

return [
    'enable_logging' => env('ENABLE_LOGGING', true),
    'middleware' => [
        'auth'
    ],
    'model' => [
        'App\Models\User' => 'name' // Name user
    ],
    'log_events' => [
        'create' => true,
        'update' => true,
        'delete' => true,
    ],
    'method' => [
        // 'GET',
        'HEAD',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ],
    'route_path' => 'database-logging',
    'assets_path' => 'assets/database-logging',
    'duration' => 30, // days
];
