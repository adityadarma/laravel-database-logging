<?php

namespace AdityaDarma\LaravelDatabaseLogging\Models;

use Illuminate\Database\Eloquent\Model;

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
        'url',
        'agent',
        'ip_address',
        'method',
        'data'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
    ];

    protected $appends = [
        'name',
        'date_create'
    ];

    public function loggable()
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

    public function getDateCreateAttribute()
    {
        return $this->created_at->format('d-m-Y H:i:s');
    }

    public function getDataAttribute()
    {
        return json_decode($this->attributes['data']);
    }
}
