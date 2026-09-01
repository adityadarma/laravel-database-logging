<?php

namespace AdityaDarma\LaravelDatabaseLogging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Log;
use Throwable;

class DatabaseLogging extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'database_loggings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'user_name',
        'host',
        'path',
        'agent',
        'ip_address',
        'method',
        'data',
        'request',
        'response',
        'query',
    ];

    protected $appends = [
        'name',
        'date_created'
    ];

    protected $casts = [
        'data' => 'array',
        'request' => 'array',
        'response' => 'array',
        'query' => 'array',
    ];

    /**
     * Resolve the connection lazily so the model always follows the
     * configured logging connection, while still allowing an explicit
     * setConnection() / on() call to win.
     *
     * @return string|null
     */
    public function getConnectionName(): ?string
    {
        return $this->connection ?: config('database-logging.connection_logging');
    }

    /**
     * Memo of resolved actors, keyed by "type|id", so one user appearing on a
     * whole page of log rows is only fetched once.
     *
     * @var array<string, Model|null>
     */
    protected static array $loggableCache = [];

    /**
     * WARNING: only usable when the logging connection is the same as the
     * connection of the related model.
     *
     * Eloquent forces the related model onto the parent's connection whenever
     * it does not declare one of its own (MorphTo::createModelByType), so with
     * a separate logging database this relation looks for the users table
     * *inside the logging database* and fails. Use resolveLoggable() instead,
     * or eager load it only when both live on the same connection.
     *
     * @return MorphTo
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo('loggable');
    }

    /**
     * Fetch the actor on its own connection.
     *
     * Deliberately does not go through the loggable() relation: building the
     * query off a fresh instance of the related class lets that class resolve
     * its own connection, which is the application default unless it declares
     * something else.
     *
     * @return Model|null
     */
    public function resolveLoggable(): ?Model
    {
        if ($this->relationLoaded('loggable')) {
            return $this->getRelation('loggable');
        }

        $class = static::loggableClass($this->loggable_type);

        if ($class === null || $this->loggable_id === null) {
            return null;
        }

        $cacheKey = $this->loggable_type . '|' . $this->loggable_id;

        if (! array_key_exists($cacheKey, static::$loggableCache)) {
            // keep the memo bounded: on a long running runtime this class
            // stays in memory for the life of the worker
            if (count(static::$loggableCache) > 1000) {
                static::$loggableCache = [];
            }

            static::$loggableCache[$cacheKey] = (new $class)->newQuery()->find($this->loggable_id);
        }

        $model = static::$loggableCache[$cacheKey];

        $this->setRelation('loggable', $model);

        return $model;
    }

    /**
     * Forget every memoised actor.
     *
     * @return void
     */
    public static function flushLoggableCache(): void
    {
        static::$loggableCache = [];
    }

    /**
     * Map a stored loggable_type onto a usable model class.
     *
     * @param string|null $type
     * @return class-string<Model>|null
     */
    protected static function loggableClass(?string $type): ?string
    {
        if (empty($type)) {
            return null;
        }

        $class = Relation::getMorphedModel($type) ?? $type;

        if (! is_string($class) || ! class_exists($class) || ! is_subclass_of($class, Model::class)) {
            return null;
        }

        return $class;
    }

    /**
     * Display name of the actor.
     *
     * Order of preference:
     * 1. eager loaded loggable relation (always fresh)
     * 2. the user_name snapshot stored on the log row
     * 3. lazy loaded loggable relation
     *
     * @return string|null
     */
    public function getNameAttribute(): ?string
    {
        try {
            if ($this->relationLoaded('loggable')) {
                $name = $this->nameFromLoggable();

                if ($name !== null && $name !== '') {
                    return $name;
                }
            }

            if ($this->user_name !== null && $this->user_name !== '') {
                return $this->user_name;
            }

            return $this->nameFromLoggable() ?? $this->user_name;
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            return $this->user_name;
        }
    }

    /**
     * Read the configured name column from the related model.
     *
     * Works across database connections because the related model
     * resolves its own connection.
     *
     * @return string|null
     */
    protected function nameFromLoggable(): ?string
    {
        $class = static::loggableClass($this->loggable_type);

        if ($class === null) {
            return null;
        }

        foreach ((array) config('database-logging.model', []) as $model => $column) {
            if ($this->loggable_type !== $model && $class !== $model) {
                continue;
            }

            $value = $this->resolveLoggable()?->getAttribute($column);

            return is_scalar($value) ? (string) $value : null;
        }

        return null;
    }

    public function getDateCreatedAttribute(): string
    {
        if ($this->created_at) {
            return $this->created_at->format('d-m-Y H:i:s');
        }

        return '';
    }
}
