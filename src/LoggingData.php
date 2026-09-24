<?php

namespace AdityaDarma\LaravelDatabaseLogging;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use AdityaDarma\LaravelDatabaseLogging\Support\PackageLogger;
use Throwable;

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
        try {
            if (! config('database-logging.enable_logging', true)) {
                return;
            }

            $files = self::normalizeFiles($request->allFiles());

            self::$request = array_replace_recursive(
                $request->except(['_token', '_method']),
                $files
            );

            if ($guard = self::getGuard()) {
                $user = auth($guard)->user();

                self::$user = [
                    'id' => $user->getKey(),
                    'class' => $user->getMorphClass(),
                    'name' => self::resolveUserName($user),
                ];
            }
        } catch (Throwable $e) {
            PackageLogger::error($e);
        }
    }

    /**
     * Replace uploaded files at any nesting depth with log-safe metadata.
     *
     * @param mixed $value
     * @return mixed
     */
    private static function normalizeFiles(mixed $value): mixed
    {
        if ($value instanceof UploadedFile) {
            return [
                'name' => $value->getClientOriginalName(),
                'size' => $value->getSize(),
                'mime_type' => $value->getMimeType(),
            ];
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = self::normalizeFiles($item);
            }
        }

        return $value;
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
        try {
            if (
                ! config('database-logging.enable_logging', true)
                || ! in_array($request->method(), (array) config('database-logging.method', []), true)
                || $response->getStatusCode() < 200
                || $response->getStatusCode() > 299
            ) {
                return;
            }

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
        } catch (Throwable $e) {
            PackageLogger::error($e);
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
        } catch (Throwable $e) {
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
