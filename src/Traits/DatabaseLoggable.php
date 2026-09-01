<?php

namespace AdityaDarma\LaravelDatabaseLogging\Traits;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use AdityaDarma\LaravelDatabaseLogging\LoggingData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait DatabaseLoggable
{
    /**
     * Log entries recorded while this model was the authenticated actor.
     *
     * Safe across connections: DatabaseLogging resolves the logging
     * connection itself, so no join is ever built between the two databases.
     *
     * @return MorphMany
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(DatabaseLogging::class, 'loggable');
    }

    /**
     * Boot model
     *
     * @return void
     */
    protected static function bootDatabaseLoggable(): void
    {
        if (config('database-logging.log_events.create', false) && config('database-logging.enable_logging', true)) {
            static::created(function (Model $model) {
                LoggingData::setData([
                    'table' => $model->getTable(),
                    'id' => $model->getKey(),
                    'event' => 'create',
                    'data' => static::getDifferentData($model->getRawOriginal(), $model->getAttributes(), 'create')
                ]);
            });
        }

        if (config('database-logging.log_events.update', false) && config('database-logging.enable_logging', true)) {
            static::updated(function (Model $model) {
                LoggingData::setData([
                    'table' => $model->getTable(),
                    'id' => $model->getKey(),
                    'event' => 'update',
                    'data' => static::getDifferentData($model->getRawOriginal(), $model->getAttributes(), 'update')
                ]);
            });
        }

        if (config('database-logging.log_events.delete', false) && config('database-logging.enable_logging', true)) {
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
            // the config is a list of column names, so it must be matched by
            // value. array_key_exists() would compare against 0,1,2... and
            // silently let password/token columns through.
            if (in_array($column, (array) config('database-logging.exclude_column_query_logging', []), true)) {
                continue;
            }

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
