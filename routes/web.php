<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => '\AdityaDarma\LaravelDatabaseLogging\Controllers',
    'middleware' => config('database-logging.middleware')
], function () {
    Route::prefix(config('database-logging.route_path'))->group(function () {
        Route::get('/', 'DatabaseLoggingController@index');
    });
});
