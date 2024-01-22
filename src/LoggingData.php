<?php

namespace AdityaDarma\LaravelDatabaseLogging;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JsonException;

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
     * @param Response $response
     * @param string|null $guard
     * @return void
     * @throws JsonException
     */
    public static function store(Request $request, Response $response, ?string $guard = null): void
    {
        if (config('database-logging.enable_logging', true) && count(self::$data)){
            $user = auth($guard)->user();
            DatabaseLogging::create([
                'loggable_id' => auth($guard)->check() ? $user->getKey() : null,
                'loggable_type' => auth($guard)->check() ? $user->getMorphClass() : null,
                'host' => $request->host(),
                'path' => $request->path(),
                'agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'method' => $request->method(),
                'data' => json_encode(self::$data, JSON_THROW_ON_ERROR),
                'request' => json_encode($request->except(['_token', '_method']), JSON_THROW_ON_ERROR),
                'response' => $response->getContent(),
            ]);
        }
    }
}
