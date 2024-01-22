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
    public static function boot(): void
    {
        parent::boot();
        if (config('database-logging.log_events.create', false)) {
            self::created(function (Model $model) {
                LoggingData::setData([
                    'table' => $model->getTable(),
                    'id' => $model->getKey(),
                    'event' => 'create',
                    'data' => self::getDifferentData($model->getRawOriginal(), $model->toArray(), 'create')
                ]);
            });
        }

        if (config('database-logging.log_events.update', false)) {
            self::updated(function (Model $model) {
                LoggingData::setData([
                    'table' => $model->getTable(),
                    'id' => $model->getKey(),
                    'event' => 'update',
                    'data' => self::getDifferentData($model->getRawOriginal(), $model->toArray(), 'update')
                ]);
            });
        }

        if (config('database-logging.log_events.delete', false)) {
            self::deleted(function (Model $model) {
                LoggingData::setData([
                    'table' => $model->getTable(),
                    'id' => $model->getKey(),
                    'event' => 'delete',
                    'data' => self::getDifferentData($model->getRawOriginal(), $model->toArray(), 'delete')
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
    public static function getDifferentData(array $old, array $new, string $event): array
    {
        $columns = count($old) ? array_keys($old) : array_keys($new);
        $result = [];
        $excludeColumn = ['id', 'created_at', 'updated_at', 'deleted_at'];

        foreach ($columns as $column) {
            if (!in_array($column, $excludeColumn)) {
                if ($event == 'create') {
                    $result[] = [
                        'column' => $column,
                        'old' => null,
                        'new' => $new[$column]
                    ];
                }
                else if ($event == 'update') {
                    if (isset($new[$column]) && $old[$column] != $new[$column]) {
                        $result[] = [
                            'column' => $column,
                            'old' => $old[$column],
                            'new' => $new[$column]
                        ];
                    }
                }
                else if ($event == 'delete') {
                    $result[] = [
                        'column' => $column,
                        'old' => $old[$column],
                        'new' => null
                    ];
                }
            }
        }

        return $result;
    }
}
