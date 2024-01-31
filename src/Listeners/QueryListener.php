<?php

namespace AdityaDarma\LaravelDatabaseLogging\Listeners;

use AdityaDarma\LaravelDatabaseLogging\LoggingData;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

class QueryListener
{
    public function handle(QueryExecuted $event): void
    {
        $sql = $event->sql;
        $bindings = $event->bindings;

        if (config('database-logging.logging_query') && !$this->shouldExcludeQuery($sql, $bindings)) {
            LoggingData::setQuery([
                'query' => $this->replaceBindings($sql, $bindings),
                'time' => $event->time
            ]);
        }
    }

    protected function shouldExcludeQuery($sql, $bindings)
    {
        foreach (config('database-logging.exclude_table_logging_query') as $excludedTable) {
            if (stripos($sql, "INSERT INTO `$excludedTable`") !== false) {
                return true;
            }
        }

        return false;
    }

    protected function replaceBindings($sql, $bindings)
    {
        $pdo = DB::getPdo();

        $modifiedSql = preg_replace_callback('/\?/', function ($matches) use ($bindings, $pdo) {
            $value = array_shift($bindings);

            if (is_numeric($value)) {
                return $value;
            } elseif (is_string($value)) {
                return $pdo->quote($value);
            } else {
                return NULL;
            }
        }, $sql);

        return $modifiedSql;
    }
}
