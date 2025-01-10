<?php

namespace AdityaDarma\LaravelDatabaseLogging\Traits;

use AdityaDarma\LaravelDatabaseLogging\LoggingData;
use Illuminate\Database\Eloquent\Model;

trait DatabaseLoggable
{
    /**
     * Boot model
     *
     * @return void
     */
    protected static function bootDatabaseLoggable(): void
    {
        if (config('database-logging.log_events.create', false)) {
            static::created(function (Model $model) {
                LoggingData::setData([
                    'table' => $model->getTable(),
                    'id' => $model->getKey(),
                    'event' => 'create',
                    'data' => static::getDifferentData($model->getRawOriginal(), $model->getAttributes(), 'create')
                ]);
            });
        }

        if (config('database-logging.log_events.update', false)) {
            static::updated(function (Model $model) {
                LoggingData::setData([
                    'table' => $model->getTable(),
                    'id' => $model->getKey(),
                    'event' => 'update',
                    'data' => static::getDifferentData($model->getRawOriginal(), $model->getAttributes(), 'update')
                ]);
            });
        }

        if (config('database-logging.log_events.delete', false)) {
            static::deleted(function (Model $model) {
                LoggingData::setData([
                    'table' => $model->getTable(),
                    'id' => $model->getKey(),
                    'event' => 'delete',
                    'data' => static::getDifferentData($model->getRawOriginal(), $model->getAttributes(), 'delete')
                ]);
            });
        }
    }

    /**
     * Find different data
     *
     * @param array $old
     * @param array $new
     * @param string $event
     * @return array
     */
    private static function getDifferentData(array $old, array $new, string $event): array
    {
        $columns = count($old) ? array_keys($old) : array_keys($new);
        $result = [];

        foreach ($columns as $column) {
            if ($event === 'create') {
                $result[] = [
                    'column' => $column,
                    'old' => null,
                    'new' => $new[$column]
                ];
            }
            else if ($event === 'update') {
                if (isset($new[$column]) && $old[$column] !== $new[$column]) {
                    $result[] = [
                        'column' => $column,
                        'old' => $old[$column],
                        'new' => $new[$column]
                    ];
                }
            }
            else if ($event === 'delete') {
                $result[] = [
                    'column' => $column,
                    'old' => $old[$column],
                    'new' => null
                ];
            }
        }

        return $result;
    }
}
