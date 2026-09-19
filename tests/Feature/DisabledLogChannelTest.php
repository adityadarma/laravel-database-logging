<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Feature;

use AdityaDarma\LaravelDatabaseLogging\LaravelDatabaseLoggingServiceProvider;
use AdityaDarma\LaravelDatabaseLogging\Support\PackageLogger;
use Illuminate\Support\Facades\Log;
use Orchestra\Testbench\TestCase as Orchestra;

class DisabledLogChannelTest extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [LaravelDatabaseLoggingServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database-logging.log.channel', null);
    }

    public function test_a_null_channel_registers_nothing(): void
    {
        $this->assertFalse(config()->has('logging.channels.database-logging'));
    }

    public function test_it_falls_back_to_the_application_default_channel(): void
    {
        $manager = Log::getFacadeRoot();

        $this->assertSame($manager, PackageLogger::resolve());
        $this->assertSame(
            $manager->channel($manager->getDefaultDriver())->getLogger(),
            PackageLogger::resolve()->getLogger()
        );
    }
}
