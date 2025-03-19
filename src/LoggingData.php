<?php

namespace AdityaDarma\LaravelDatabaseLogging;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JsonException;

class LoggingData
{
    private static array $user = ['id' => null, 'class' => null];
    private static array $request = [];
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
     * Save request
     *
     * @param Request $request
     * @return void
     */
    public static function request(Request $request): void
    {
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

        self::$request = array_merge($request->except(['_token', '_method']), $filesArray);

        if ($guard = self::getGuard()) {
            self::$user = [
                'id' => auth($guard)->user()->getKey(),
                'class' => auth($guard)->user()->getMorphClass(),
            ];
        }
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

                DatabaseLogging::create([
                    'loggable_id' => self::$user['id'] ?? null,
                    'loggable_type' => self::$user['class'] ?? null,
                    'host' => $request->getSchemeAndHttpHost(),
                    'path' => $request->path(),
                    'agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'method' => $request->method(),
                    'data' => self::$data,
                    'request' => self::$request,
                    'response' => $request->expectsJson() ? [json_decode($response->getContent())] : [],
                    'query' => self::$query,
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
        $guards = config('auth.guards');
        foreach(array_keys($guards) as $guard){
            if(auth($guard)->check()){
                return $guard;
            }
        }

        return null;
    }
}
