<?php

namespace AdityaDarma\LaravelDatabaseLogging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use JsonException;

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
        'date_create'
    ];

    public function loggable(): MorphTo
    {
        return $this->morphTo('loggable');
    }

    public function getNameAttribute()
    {
        foreach (config('database-logging.model') as $model => $name) {
            if ($this->loggable_type == $model) {
                return $this->loggable->$name;
            }
        }
        return '-';
    }

    public function getDateCreateAttribute(): string
    {
        if ($this->created_at) {
            return $this->created_at->format('d-m-Y H:i:s');
        }
        return '';
    }

    /**
     * @throws JsonException
     */
    public function getDataAttribute()
    {
        return json_decode($this->attributes['data'], true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public function getRequestAttribute()
    {
        return json_decode($this->attributes['request'], true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public function getResponseAttribute()
    {
        return json_decode($this->attributes['response'], true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public function getDataObjectAttribute()
    {
        return json_decode($this->attributes['data'], false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public function getRequestObjectAttribute()
    {
        return json_decode($this->attributes['request'], false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public function getResponseObjectAttribute()
    {
        return json_decode($this->attributes['response'], false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public function getQueryObjectAttribute()
    {
        return json_decode($this->attributes['query']);
    }
}
