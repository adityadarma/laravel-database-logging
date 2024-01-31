<?php

return [
    'enable_logging' => env('ENABLE_LOGGING', true),
    'logging_query' => env('LOGGING_QUERY', false),
    'exclude_table_logging_query' => [

    ],
    'middleware' => [
        'web',
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
