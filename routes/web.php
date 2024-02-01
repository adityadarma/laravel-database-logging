<?php

use Illuminate\Support\Facades\Route;

Route::middleware(config('database-logging.middleware'))
    ->prefix(config('database-logging.route_path'))->group(function () {
        Route::get('/', [config('database-logging.route_controller'), 'index']);
});
