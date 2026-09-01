<?php

namespace AdityaDarma\LaravelDatabaseLogging\Listeners;

use AdityaDarma\LaravelDatabaseLogging\LoggingData;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Stringable;
use Throwable;

class QueryListener
{
    public function handle(QueryExecuted $event): void
    {
        $sql = $event->sql;
        $bindings = $event->bindings;

        if (config('database-logging.query_logging') && !$this->shouldExcludeQuery($sql)) {
            LoggingData::setQuery([
                'query' => $this->replaceBindings($sql, $bindings, $event->connection),
                'time' => $event->time
            ]);
        }
    }

    /**
     * Decide whether a query touches an excluded table.
     *
     * Identifier quoting differs per driver (`t` on MySQL, "t" on Postgres and
     * SQLite, [t] on SQL Server), so the SQL is stripped of quoting characters
     * before matching instead of assuming MySQL backticks.
     *
     * @param string $sql
     * @return bool
     */
    protected function shouldExcludeQuery($sql): bool
    {
        $tables = (array) config('database-logging.exclude_table_query_logging', []);

        if ($tables === []) {
            return false;
        }

        $normalized = str_replace(['`', '"', '[', ']'], '', (string) $sql);

        foreach ($tables as $excludedTable) {
            if (! is_string($excludedTable) || $excludedTable === '') {
                continue;
            }

            $table = preg_quote($excludedTable, '/');

            // table names only ever follow one of these keywords, which keeps
            // a column or a string literal of the same name from matching
            if (preg_match('/\b(?:from|into|update|join|table)\s+(?:\w+\.)?' . $table . '\b/i', $normalized)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Inline the bindings into the SQL for human readable output.
     *
     * @param string $sql
     * @param array $bindings
     * @param Connection|null $connection
     * @return string
     */
    protected function replaceBindings($sql, $bindings, ?Connection $connection = null): string
    {
        // Named bindings come back keyed by name, so reindex before walking
        // the placeholders positionally.
        $bindings = array_values((array) $bindings);

        $modifiedSql = '';
        $bindingIndex = 0;
        $iMax = strlen($sql);

        for ($i = 0; $i < $iMax; $i++) {
            // array_key_exists, not isset: a null binding is still a binding
            // and must consume its placeholder, otherwise every value after
            // it lands on the wrong column.
            if ($sql[$i] === '?' && array_key_exists($bindingIndex, $bindings)) {
                $modifiedSql .= $this->formatBinding($bindings[$bindingIndex++], $connection);
            } else {
                $modifiedSql .= $sql[$i];
            }
        }

        return $modifiedSql;
    }

    /**
     * Render a single binding as a SQL literal.
     *
     * @param mixed $value
     * @param Connection|null $connection
     * @return string
     */
    protected function formatBinding(mixed $value, ?Connection $connection = null): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        // only real numbers are emitted bare; a numeric *string* such as a
        // phone number or leading-zero code must stay quoted
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        } elseif ($value instanceof BackedEnum) {
            $value = $value->value;
        } elseif ($value instanceof Stringable || (is_object($value) && method_exists($value, '__toString'))) {
            $value = (string) $value;
        }

        if (! is_scalar($value)) {
            return "'?'";
        }

        return $this->quote((string) $value, $connection);
    }

    /**
     * Quote a string literal using the connection that ran the query.
     *
     * @param string $value
     * @param Connection|null $connection
     * @return string
     */
    protected function quote(string $value, ?Connection $connection = null): string
    {
        try {
            // quote with the connection that actually ran the query, not the
            // default one, otherwise a separate logging connection gets opened
            // just to quote a string and the wrong rules are applied
            $pdo = $connection ? $connection->getPdo() : DB::getPdo();

            return $pdo->quote($value);
        } catch (Throwable $e) {
            return "'" . str_replace("'", "''", $value) . "'";
        }
    }
}
