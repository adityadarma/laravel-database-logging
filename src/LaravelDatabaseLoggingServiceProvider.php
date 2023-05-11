<?php

namespace AdityaDarma\LaravelDatabaseLogging;

use AdityaDarma\LaravelDatabaseLogging\Console\DatabaseLoggingInstall;
use Illuminate\Support\ServiceProvider;

class LaravelDatabaseLoggingServiceProvider extends ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/config/database-logging.php';
    const MIGRATION_PATH = __DIR__ . '/migrations';


    /**
     * Publish data.
     *
     * @return void
     */
    private function publish()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('database-logging.php')
        ], 'config');

        $this->publishes([
            self::MIGRATION_PATH => database_path('migrations')
        ], 'migrations');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publish();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'database-logging'
        );

        $this->app->singleton(LoggingData::class, function($app){
            return new LoggingData();
        });

        $this->commands([DatabaseLoggingInstall::class]);
    }
}
