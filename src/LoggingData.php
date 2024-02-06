<?php

namespace AdityaDarma\LaravelDatabaseLogging;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use JsonException;

class LoggingData
{
    private static array $data = [];
    private static array $query = [];

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
     * Save query to array
     *
     * @param array $data
     * @return void
     */
    public static function setQuery(array $data): void
    {
        self::$query[] = $data;
    }

    /**
     * Store data to database
     *
     * @param Request $request
     * @param $response
     * @return void
     * @throws JsonException
     */
    public static function store(Request $request, $response): void
    {
        if (
            config('database-logging.enable_logging', true)
            && in_array($request->method(), config('database-logging.method'), true)
            && count(self::$data)
        ){
            try {
                $guard = self::getGuard();

                // Upload file
                $files = $request->allFiles();
                $filesArray = [];
                foreach ($files as $key => $file) {
                    if (is_array($file)) {
                        foreach ($file as $item) {
                            $filesArray[$key][] = [
                                'name' => $item->getClientOriginalName(),
                                'size' => $item->getSize(),
                                'mime_type' => $item->getMimeType(),
                            ];
                        }
                    }
                    else {
                        $filesArray[$key] = [
                            'name' => $file->getClientOriginalName(),
                            'size' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                        ];
                    }
                }

                DatabaseLogging::create([
                    'loggable_id' => $guard ? auth($guard)->user()->getKey() : null,
                    'loggable_type' => $guard ? auth($guard)->user()->getMorphClass() : null,
                    'host' => $request->getHost(),
                    'path' => $request->path(),
                    'agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'method' => $request->method(),
                    'data' => json_encode(self::$data, JSON_THROW_ON_ERROR),
                    'request' => json_encode(array_merge($request->except(['_token', '_method']), $filesArray), JSON_THROW_ON_ERROR),
                    'response' => $request->expectsJson() ? $response->getContent() : json_encode([], JSON_THROW_ON_ERROR),
                    'query' => json_encode(self::$query, JSON_THROW_ON_ERROR),
                ]);
            } catch (Exception $e){
                Log::error($e->getMessage());
            }
        }
    }

    /**
     * Get guard login
     *
     * @return string|null
     */
    public static function getGuard(): ?string
    {
        $guards = config('database-logging.guards');
        foreach($guards as $guard){
            if(auth($guard)->check()){
                return $guard;
            }
        }

        return null;
    }
}
