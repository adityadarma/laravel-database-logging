<?php

use Illuminate\Support\Facades\Route;

Route::middleware(config('database-logging.middleware'))
    ->controller(config('database-logging.route_controller'))
    ->prefix(config('database-logging.route_path'))
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/datatable', 'datatable');
});
