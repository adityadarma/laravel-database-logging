<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Feature;

use AdityaDarma\LaravelDatabaseLogging\LaravelDatabaseLoggingServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class ApplicationDefinedLogChannelTest extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [LaravelDatabaseLoggingServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // an application that already owns this channel name must keep its
        // own definition
        $app['config']->set('logging.channels.database-logging', [
            'driver' => 'single',
            'path' => storage_path('logs/app-owned.log'),
        ]);
    }

    public function test_an_existing_application_channel_wins(): void
    {
        $this->assertSame('single', config('logging.channels.database-logging.driver'));
        $this->assertStringEndsWith(
            'app-owned.log',
            config('logging.channels.database-logging.path')
        );
    }
}
