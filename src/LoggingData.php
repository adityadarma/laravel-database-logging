<?php

namespace AdityaDarma\LaravelDatabaseLogging;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LoggingData
{
    private static $data = [];

    public static function setData(array $data): void
    {
        self::$data[] = $data;
    }


    public static function store(Request $request, $response): void
    {
        if (
            auth()->check()
            && config('database-logging.enable_logging', true)
            && request()->method() != 'GET'
        ){
            if(count(self::$data)) {
                $auth = auth()->user();
                DatabaseLogging::create([
                    'loggable_id' => $auth->getKey(),
                    'loggable_type' => $auth->getMorphClass(),
                    'url' => request()->fullUrl(),
                    'agent' => request()->userAgent(),
                    'ip_address' => request()->ip(),
                    'method' => request()->method(),
                    'data' => json_encode(self::$data),
                    'request' => json_encode($request->except(['_token','_method'])),
                    'response' => $response->getContent(),
                ]);
            }
        }
    }
}
