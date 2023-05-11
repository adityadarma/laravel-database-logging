<?php

namespace AdityaDarma\LaravelDatabaseLogging\Traits;
use AdityaDarma\LaravelDatabaseLogging\LoggingData;

trait DatabaseLogable
{
    public static function boot() {
        parent::boot();
        if (config('database-logging.log_events.create', false)) {
            self::created(function ($model) {
                LoggingData::setData([
                    'table' => $model->getTable(),
                    'type' => 'create',
                    'data' => $model
                ]);
            });
        }

        if (config('database-logging.log_events.update', false)) {
            self::updated(function ($model) {
                LoggingData::setData([
                    'table' => $model->getTable(),
                    'type' => 'update',
                    'data' => $model->getRawOriginal()
                ]);
            });
        }


        if (config('database-logging.log_events.delete', false)) {
            self::deleted(function ($model) {
                LoggingData::setData([
                    'table' => $model->getTable(),
                    'type' => 'delete',
                    'data' => $model->getRawOriginal()
                ]);
            });
        }
    }
}
