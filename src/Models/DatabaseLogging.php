<?php

namespace AdityaDarma\LaravelDatabaseLogging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Log;

class DatabaseLogging extends Model
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection;

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

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected array $dates = [
        'created_at',
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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->connection = config('database-logging.connection_logging');
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo('loggable');
    }

    public function getNameAttribute(): string | null
    {
        try {
            foreach (config('database-logging.model') as $model => $name) {
                if ($this->loggable_type === $model && config('database.default') === config('database-logging.connection_logging')) {
                    return $this->loggable->$name ?? '';
                }
            }
            return $this->user_name;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->user_name;
        }
    }

    public function getDateCreatedAttribute(): string
    {
        if ($this->created_at) {
            return $this->created_at->format('d-m-Y H:i:s');
        }
        return '';
    }
}
