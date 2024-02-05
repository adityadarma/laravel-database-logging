<?php

return [
    'enable_logging' => env('ENABLE_LOGGING', true),
    'logging_query' => env('LOGGING_QUERY', false),
    'morph_key_type' => 'int', // available int, uuid, ulid
    'exclude_table_logging_query' => [

    ],
    'middleware' => [
        'web',
        'auth'
    ],
    'guards' => [
        'web'
    ],
    'model' => [
        App\Models\User::class => 'name' // Name user
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
    'route_controller' => AdityaDarma\LaravelDatabaseLogging\Controllers\DatabaseLoggingController::class,
    'route_path' => 'database-logging',
    'assets_path' => 'assets/database-logging',
    'duration' => 30, // days
];
