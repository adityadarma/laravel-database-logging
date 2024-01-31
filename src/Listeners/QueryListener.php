<?php

namespace AdityaDarma\LaravelDatabaseLogging\Listeners;

use AdityaDarma\LaravelDatabaseLogging\LoggingData;
use Illuminate\Database\Events\QueryExecuted;

class QueryListener
{
    public function handle(QueryExecuted $event): void
    {
        if (config('database-logging.logging_query')) {
            LoggingData::setQuery([
                'query' => $event->sql,
                'time' => $event->time
            ]);
        }
    }
}