<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests;

use AdityaDarma\LaravelDatabaseLogging\LaravelDatabaseLoggingServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelDatabaseLoggingServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('database-logging.enabled', true);
        $app['config']->set('database-logging.query_logging', true);
        $app['config']->set('database-logging.exclude_table_query_logging', ['database_loggings']);
    }
}
