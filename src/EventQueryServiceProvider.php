<?php

namespace AdityaDarma\LaravelDatabaseLogging;

use AdityaDarma\LaravelDatabaseLogging\Listeners\QueryListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Database\Events\QueryExecuted;

class EventQueryServiceProvider extends ServiceProvider
{
    protected $listen = [
        QueryExecuted::class => [
            QueryListener::class,
        ],
    ];
}
