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

        if (config('database-logging.logging_query') && !$this->shouldExcludeQuery($sql)) {
            LoggingData::setQuery([
                'query' => $this->replaceBindings($sql, $bindings),
                'time' => $event->time
            ]);
        }
    }

    protected function shouldExcludeQuery($sql): bool
    {
        foreach (config('database-logging.exclude_table_logging_query') as $excludedTable) {
            if (stripos($sql, "INSERT INTO `$excludedTable`") !== false) {
                return true;
            }
        }

        return false;
    }

    protected function replaceBindings($sql, $bindings): string
    {
        $pdo = DB::getPdo();

        $modifiedSql = '';
        $bindingIndex = 0;
        $iMax = strlen($sql);

        for ($i = 0;  $i < $iMax; $i++) {
            if ($sql[$i] === '?' && isset($bindings[$bindingIndex])) {
                $value = $bindings[$bindingIndex++];

                if (is_numeric($value)) {
                    $modifiedSql .= $value;
                } elseif (is_string($value)) {
                    $modifiedSql .= $pdo->quote($value);
                } else {
                    $modifiedSql .= NULL;
                }
            } else {
                $modifiedSql .= $sql[$i];
            }
        }

        return $modifiedSql;
    }
}
