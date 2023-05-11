<?php

namespace AdityaDarma\LaravelDatabaseLogging;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;

class LoggingData
{
    protected array $data = [];

    public static function setData(array $data): array
    {
        return self::$data[] = $data;
    }


    public static function store(): void
    {
        if (auth()->check() || config('database-logging.enable_logging', true) || request()->method() != 'GET'){
            $auth = auth()->user();
            DatabaseLogging::create([
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
}
