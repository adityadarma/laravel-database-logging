<?php

return [
    'enable_logging' => env('ENABLE_LOGGING', true),
    'query_logging' => env('QUERY_LOGGING', false),
    'morph_key_type' => 'int', // available int, uuid, ulid
    'connection_logging' => env('DB_CONNECTION_LOGGING', env('DB_CONNECTION', 'sqlite')), // database connection for logging
    'exclude_table_query_logging' => [
        'migrations',
        'database_loggings',
    ],
    'exclude_column_query_logging' => [
        'password',
        'remember_token',
        'token',
    ],
    'middleware' => [
        'web',
        'auth'
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
    'route_path' => '/database-logging',
    'duration' => env('DURATION_LOGGING', 30), // days
];
