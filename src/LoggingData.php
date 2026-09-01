<?php

namespace AdityaDarma\LaravelDatabaseLogging;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JsonException;

class LoggingData
{
    private static array $user = ['id' => null, 'class' => null, 'name' => null];
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
     * Get query data
     *
     * @return array
     */
    public static function getQuery(): array
    {
        return self::$query;
    }

    /**
     * Get model event data
     *
     * @return array
     */
    public static function getData(): array
    {
        return self::$data;
    }

    /**
     * Reset all static data
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$user = ['id' => null, 'class' => null, 'name' => null];
        self::$request = [];
        self::$data = [];
        self::$query = [];
    }

    /**
     * Save request
     *
     * @param Request $request
     * @return void
     */
    public static function request(Request $request): void
    {
        if (config('database-logging.enable_logging', true)) {
            try {
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
                    $user = auth($guard)->user();

                    self::$user = [
                        'id' => $user->getKey(),
                        'class' => $user->getMorphClass(),
                        'name' => self::resolveUserName($user),
                    ];
                }
            } catch (Exception $e){
                Log::error($e->getMessage());
            }
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
            && $response->getStatusCode() >= 200
            && $response->getStatusCode() <= 299
        ) {
            try {
                DatabaseLogging::create([
                    'loggable_id' => self::$user['id'] ?? null,
                    'loggable_type' => self::$user['class'] ?? null,
                    'user_name' => self::$user['name'] ?? null,
                    'host' => $request->getSchemeAndHttpHost(),
                    'path' => $request->path(),
                    'agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'method' => $request->method(),
                    'data' => self::$data,
                    'request' => self::$request,
                    'response' => $request->expectsJson() ? json_decode($response->getContent()) : [],
                    'query' => self::$query,
                ]);
            } catch (Exception $e){
                Log::error($e->getMessage());
            }
        }
    }

    /**
     * Take a snapshot of the actor name at request time.
     *
     * This is what makes a separate logging database work: the log row keeps
     * its own copy of the name, so the UI never has to reach into another
     * connection to render it.
     *
     * @param mixed $user
     * @return string|null
     */
    private static function resolveUserName(mixed $user): ?string
    {
        if (! is_object($user)) {
            return null;
        }

        $models = (array) config('database-logging.model', []);
        $class = $user::class;
        $morphClass = method_exists($user, 'getMorphClass') ? $user->getMorphClass() : $class;

        // exact class / morph alias match first
        foreach ($models as $model => $column) {
            if ($class === $model || $morphClass === $model) {
                return self::readAttribute($user, $column);
            }
        }

        // then allow subclasses of a configured model
        foreach ($models as $model => $column) {
            if (is_string($model) && class_exists($model) && $user instanceof $model) {
                return self::readAttribute($user, $column);
            }
        }

        return self::readAttribute($user, 'name') ?? self::readAttribute($user, 'email');
    }

    /**
     * Safely read a scalar attribute from an object.
     *
     * @param object $user
     * @param string $column
     * @return string|null
     */
    private static function readAttribute(object $user, string $column): ?string
    {
        try {
            $value = $user instanceof Model
                ? $user->getAttribute($column)
                : ($user->{$column} ?? null);
        } catch (Exception $e) {
            return null;
        }

        return is_scalar($value) ? (string) $value : null;
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
