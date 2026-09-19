<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Feature;

use AdityaDarma\LaravelDatabaseLogging\LaravelDatabaseLoggingServiceProvider;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\RotatingFileHandler;
use Orchestra\Testbench\TestCase as Orchestra;

class SelfRegisteredLogChannelTest extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [LaravelDatabaseLoggingServiceProvider::class];
    }

    public function test_it_registers_its_log_channel_without_application_config(): void
    {
        $this->assertTrue(
            config()->has('logging.channels.database-logging'),
            'the package should inject its channel into logging.channels'
        );

        $this->assertSame('daily', config('logging.channels.database-logging.driver'));
        $this->assertStringEndsWith(
            'database-logging.log',
            config('logging.channels.database-logging.path')
        );
    }

    public function test_the_registered_channel_is_resolvable_by_monolog(): void
    {
        $manager = Log::getFacadeRoot();

        $this->assertInstanceOf(LogManager::class, $manager);

        $handler = $manager->channel('database-logging')->getLogger()->getHandlers()[0];

        $this->assertInstanceOf(RotatingFileHandler::class, $handler);
    }
}
