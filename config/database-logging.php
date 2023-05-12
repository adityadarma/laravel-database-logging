<?php

return [
    'enable_logging' => true,
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
    'duration' => 10,
];
