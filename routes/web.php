<?php

use Illuminate\Support\Facades\Route;

Route::middleware(config('database-logging.middleware'))
    ->controller(config('database-logging.route_controller'))
    ->group(function () {
        Route::get(config('database-logging.route_path'), 'index');
});
