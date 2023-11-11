<?php

namespace AdityaDarma\LaravelDatabaseLogging;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use Illuminate\Http\Request;

class LoggingData
{
    private static array $data = [];

    /**
     * Save data to array
     *
     * @param array $data
     * @return void
     */
    public static function setData(array $data): void
    {
        self::$data[] = $data;
    }

    /**
     * Store data to database
     *
     * @param Request $request
     * @param $response
     * @return void
     */
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
                    'host' => request()->host(),
                    'path' => request()->path(),
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
