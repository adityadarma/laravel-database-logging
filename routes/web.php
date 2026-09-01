<?php

use Illuminate\Support\Facades\Route;

Route::middleware(config('database-logging.middleware'))
    ->controller(config('database-logging.route_controller'))
    ->prefix(config('database-logging.route_path'))
    ->name('database-logging.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/datatable', 'datatable')->name('datatable');
});
