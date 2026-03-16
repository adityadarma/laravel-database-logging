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
}
