<?php

namespace AdityaDarma\LaravelDatabaseLogging;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;

class LoggingData
{
    protected $data = [];

    public static function setData(array $data)
    {
        return app(LoggingData::class)->data = $data;
    }


    public static function store()
    {
        if (!auth()->check() || !config('database-logging.enable_logging', true)) return;

        $auth = auth()->user();
        return DatabaseLogging::create([
            'loggable_id' => $auth->getKey(),
            'loggable_type' => $auth->getMorphClass(),
            'url' => request()->fullUrl(),
            'agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
            'method' => request()->method(),
            'data' => json_encode(app(LoggingData::class)->data)
        ]);
    }
}
