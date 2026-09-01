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

    public function loggable(): MorphTo
    {
        return $this->morphTo('loggable');
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
        if (empty($this->loggable_type)) {
            return null;
        }

        $class = Relation::getMorphedModel($this->loggable_type) ?? $this->loggable_type;

        foreach ((array) config('database-logging.model', []) as $model => $column) {
            if ($this->loggable_type !== $model && $class !== $model) {
                continue;
            }

            if (! class_exists($class)) {
                return null;
            }

            $value = $this->loggable?->getAttribute($column);

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
