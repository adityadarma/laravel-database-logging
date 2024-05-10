<?php

use Illuminate\Support\Facades\Route;

Route::middleware(config('database-logging.middleware'))
    ->prefix(config('database-logging.route_path'))
    ->controller(config('database-logging.route_controller'))
    ->group(function () {
        Route::get('/', 'index');
});
