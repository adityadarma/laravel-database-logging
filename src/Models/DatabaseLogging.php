<?php

namespace AdityaDarma\LaravelDatabaseLogging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
        'loggable_id',
        'loggable_type',
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

    public function loggable(): MorphTo
    {
        return $this->morphTo('loggable');
    }

    public function getNameAttribute(): string
    {
        foreach (config('database-logging.model') as $model => $name) {
            if ($this->loggable_type === $model) {
                return $this->loggable->$name ?? '';
            }
        }
        return '';
    }

    public function getDateCreatedAttribute(): string
    {
        if ($this->created_at) {
            return $this->created_at->format('d-m-Y H:i:s');
        }
        return '';
    }
}
