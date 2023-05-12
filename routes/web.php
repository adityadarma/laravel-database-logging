<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => '\AdityaDarma\LaravelDatabaseLogging\Controllers',
    'middleware' => config('database-logging.middleware')
], function () {
    Route::match(['get', 'post'], config('database-logging.route_path'), 'DatabaseLoggingController@index');
});
