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
    protected $casts = [
        'created_at' => 'datetime',
    ];

}
