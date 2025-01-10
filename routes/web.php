<?php

use Illuminate\Support\Facades\Route;

Route::middleware(config('database-logging.middleware'))
    ->group(function () {
        Route::get(config('database-logging.route_path'), [config('database-logging.route_controller'), 'index']);
});
