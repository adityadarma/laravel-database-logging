<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Unit\Listeners;

use AdityaDarma\LaravelDatabaseLogging\Listeners\QueryListener;
use AdityaDarma\LaravelDatabaseLogging\LoggingData;
use AdityaDarma\LaravelDatabaseLogging\Tests\TestCase;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Mockery;

class QueryListenerTest extends TestCase
{
    public function test_listener_handles_query_executed_event(): void
    {
        config(['database-logging.query_logging' => true]);
        config(['database-logging.exclude_table_query_logging' => []]);

        $event = new QueryExecuted(
            'SELECT * FROM users WHERE id = ?',
            [1],
            100,
            DB::connection()
        );

        $listener = new QueryListener();
        $listener->handle($event);

        $queries = LoggingData::getQuery();
        $this->assertNotEmpty($queries);
    }

    public function test_listener_excludes_configured_tables(): void
    {
        config(['database-logging.query_logging' => true]);
        config(['database-logging.exclude_table_query_logging' => ['database_loggings']]);

        $event = new QueryExecuted(
            'INSERT INTO `database_loggings` (method, url) VALUES (?, ?)',
            ['GET', '/test'],
            50,
            DB::connection()
        );

        $listener = new QueryListener();
        $listener->handle($event);

        $queries = LoggingData::getQuery();
        $this->assertEmpty($queries);
    }

    public function test_listener_replaces_bindings_in_query(): void
    {
        config(['database-logging.query_logging' => true]);
        config(['database-logging.exclude_table_query_logging' => []]);

        $event = new QueryExecuted(
            'SELECT * FROM users WHERE name = ? AND age = ?',
            ['John Doe', 25],
            75,
            DB::connection()
        );

        $listener = new QueryListener();
        $listener->handle($event);

        $queries = LoggingData::getQuery();
        $this->assertNotEmpty($queries);
        $this->assertStringContainsString('John Doe', $queries[0]['query']);
        $this->assertStringContainsString('25', $queries[0]['query']);
    }

    public function test_listener_does_not_log_when_disabled(): void
    {
        config(['database-logging.query_logging' => false]);

        $event = new QueryExecuted(
            'SELECT * FROM users',
            [],
            50,
            DB::connection()
        );

        $listener = new QueryListener();
        $listener->handle($event);

        $queries = LoggingData::getQuery();
        $this->assertEmpty($queries);
    }

    /**
     * The exclusion used to be matched against MySQL backticks only, which
     * silently disabled it on every other driver.
     */
    public function test_exclusion_is_independent_of_identifier_quoting(): void
    {
        config(['database-logging.query_logging' => true]);
        config(['database-logging.exclude_table_query_logging' => ['database_loggings']]);

        $variants = [
            'INSERT INTO `database_loggings` (method) VALUES (?)',
            'insert into "database_loggings" ("method") values (?)',
            'INSERT INTO [database_loggings] ([method]) VALUES (?)',
            'select * from "database_loggings" where "id" = ?',
            'delete from database_loggings where id = ?',
        ];

        $listener = new QueryListener();

        foreach ($variants as $sql) {
            LoggingData::reset();
            $listener->handle(new QueryExecuted($sql, [1], 10, DB::connection()));

            $this->assertEmpty(LoggingData::getQuery(), "not excluded: $sql");
        }
    }

    public function test_a_column_named_like_an_excluded_table_is_still_logged(): void
    {
        config(['database-logging.query_logging' => true]);
        config(['database-logging.exclude_table_query_logging' => ['migrations']]);

        $listener = new QueryListener();
        $listener->handle(new QueryExecuted(
            'select * from "projects" where "migrations" = ?',
            [1],
            10,
            DB::connection()
        ));

        $this->assertNotEmpty(LoggingData::getQuery());
    }

    /**
     * isset() skips null bindings, which left the placeholder in place and
     * shifted every following value onto the wrong column.
     */
    public function test_null_binding_consumes_its_placeholder(): void
    {
        config(['database-logging.query_logging' => true]);
        config(['database-logging.exclude_table_query_logging' => []]);

        $listener = new QueryListener();
        $listener->handle(new QueryExecuted(
            'update "users" set "deleted_at" = ?, "name" = ? where "id" = ?',
            [null, 'Aditya', 7],
            10,
            DB::connection()
        ));

        $query = LoggingData::getQuery()[0]['query'];

        $this->assertStringContainsString("set \"deleted_at\" = null", $query);
        $this->assertStringContainsString("\"name\" = 'Aditya'", $query);
        $this->assertStringContainsString('"id" = 7', $query);
        $this->assertStringNotContainsString('?', $query);
    }

    public function test_numeric_string_binding_stays_quoted(): void
    {
        config(['database-logging.query_logging' => true]);
        config(['database-logging.exclude_table_query_logging' => []]);

        $listener = new QueryListener();
        $listener->handle(new QueryExecuted(
            'select * from "users" where "phone" = ? and "age" = ?',
            ['08123456789', 25],
            10,
            DB::connection()
        ));

        $query = LoggingData::getQuery()[0]['query'];

        // a leading zero is lost the moment the value is emitted bare
        $this->assertStringContainsString("\"phone\" = '08123456789'", $query);
        $this->assertStringContainsString('"age" = 25', $query);
    }

    public function test_boolean_and_datetime_bindings_are_rendered(): void
    {
        config(['database-logging.query_logging' => true]);
        config(['database-logging.exclude_table_query_logging' => []]);

        $listener = new QueryListener();
        $listener->handle(new QueryExecuted(
            'update "users" set "active" = ?, "seen_at" = ? where "id" = ?',
            [true, new \DateTimeImmutable('2026-08-31 09:30:00'), 1],
            10,
            DB::connection()
        ));

        $query = LoggingData::getQuery()[0]['query'];

        $this->assertStringContainsString('"active" = 1', $query);
        $this->assertStringContainsString("'2026-08-31 09:30:00'", $query);
        $this->assertStringNotContainsString('?', $query);
    }
}
