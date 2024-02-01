<?php

namespace AdityaDarma\LaravelDatabaseLogging;

use AdityaDarma\LaravelDatabaseLogging\Console\DatabaseLoggingInstall;
use AdityaDarma\LaravelDatabaseLogging\Console\DatabaseLoggingPurge;
use AdityaDarma\LaravelDatabaseLogging\Middleware\CaptureLogging;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;

class LaravelDatabaseLoggingServiceProvider extends ServiceProvider
{
    public const CONFIG_PATH = __DIR__ . '/../config/database-logging.php';
    public const MIGRATION_PATH = __DIR__ . '/../database/migrations';
    public const ROUTE_PATH = __DIR__ . '/../routes';
    public const VIEW_PATH = __DIR__ . '/../views';
    public const PUBLIC_PATH = __DIR__ . '/../public';

    /**
     * Publish data.
     *
     * @return void
     */
    private function publish(): void
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('database-logging.php')
        ], 'config');

        $this->publishes([
            self::MIGRATION_PATH => database_path('migrations')
        ], 'migrations');

        $this->publishes([
            self::PUBLIC_PATH => public_path(config('database-logging.assets_path'))
        ], 'assets');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publish();

        $this->loadRoutesFrom(self::ROUTE_PATH . '/web.php');
        $this->loadViewsFrom(self::VIEW_PATH, 'LaravelDatabaseLogging');
    }

    /**
     * Register services.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'database-logging'
        );

        $this->app->singleton(LoggingData::class, function() {
            return new LoggingData();
        });

        $this->commands([DatabaseLoggingInstall::class]);
        $this->commands([DatabaseLoggingPurge::class]);

        $this->app->make('router')->aliasMiddleware(
                'capture-logging',
                CaptureLogging::class
            );
    }
}
