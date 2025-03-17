<?php

return [
    'enable_logging' => env('ENABLE_LOGGING', true),
    'logging_query' => env('LOGGING_QUERY', false),
    'morph_key_type' => 'int', // available int, uuid
    'database_connection' => env('DB_CONNECTION', 'forge'),
    'exclude_table_logging_query' => [
        'migrations'
    ],
    'exclude_column_logging_query' => [
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
    'assets_path' => 'assets/database-logging',
    'duration' => 30, // days
];
