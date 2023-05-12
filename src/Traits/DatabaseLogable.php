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
                    'data' => self::findDifferentData($model->getRawOriginal(), $model->toArray(), 'create')
                ]);
            });
        }

        if (config('database-logging.log_events.update', false)) {
            self::updated(function ($model) {
                LoggingData::setData([
                    'table' => $model->getTable(),
                    'type' => 'update',
                    'data' => self::findDifferentData($model->getRawOriginal(), $model->toArray(), 'update')
                ]);
            });
        }

        if (config('database-logging.log_events.delete', false)) {
            self::deleted(function ($model) {
                LoggingData::setData([
                    'table' => $model->getTable(),
                    'type' => 'delete',
                    'data' => self::findDifferentData($model->getRawOriginal(), $model->toArray(), 'delete')
                ]);
            });
        }
    }

    public static function findDifferentData(array $old, array $new, string $type)
    {
        $columns = count($old) ? array_keys($old) : array_keys($new);
        $result = [];
        $excludeColumn = ['id', 'created_at', 'updated_at', 'deleted_at'];

        foreach ($columns as $column) {
            if (!in_array($column, $excludeColumn)) {
                if ($type == 'create') {
                    $result[] = [
                        'column' => $column,
                        'old' => null,
                        'new' => $new[$column]
                    ];
                } else if ($type == 'update') {
                    if ($old[$column] != $new[$column]) {
                        $result[] = [
                            'column' => $column,
                            'old' => $old[$column],
                            'new' => $new[$column]
                        ];
                    }
                } else if ($type == 'delete') {
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
