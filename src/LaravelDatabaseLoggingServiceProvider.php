<?php

namespace AdityaDarma\LaravelDatabaseLogging;

use AdityaDarma\LaravelDatabaseLogging\Console\DatabaseLoggingInstall;
use AdityaDarma\LaravelDatabaseLogging\Console\DatabaseLoggingPurge;
use AdityaDarma\LaravelDatabaseLogging\Middleware\CaptureLogging;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class LaravelDatabaseLoggingServiceProvider extends ServiceProvider
{
    public const CONFIG_PATH = __DIR__ . '/../config/database-logging.php';
    public const MIGRATION_PATH = __DIR__ . '/../database/migrations';
    public const ROUTE_PATH = __DIR__ . '/../routes';
    public const VIEW_PATH = __DIR__ . '/../views';
    public const ASSET_PATH = __DIR__ . '/../public';

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
            self::ASSET_PATH => public_path(config('database-logging.assets_path'))
        ], 'assets');
    }

    /**
     * Register the package log channel into logging.channels at runtime.
     *
     * Keeps the package self-contained: a host application never has to add a
     * channel to config/logging.php. A channel already defined under the same
     * name always wins.
     *
     * @return void
     */
    private function registerLogChannel(): void
    {
        $config = $this->app['config'];
        $log = $config->get('database-logging.log', []);
        $name = $log['channel'] ?? null;

        if (empty($name)) {
            return;
        }

        if ($config->has("logging.channels.{$name}")) {
            return;
        }

        unset($log['channel']);

        $config->set("logging.channels.{$name}", $log);
    }

    /**
     * @throws BindingResolutionException
     */
    private function registerMiddlewareAlias(): void
    {
        $this->app->make(Router::class)
            ->aliasMiddleware(
                'capture-logging',
                CaptureLogging::class
            );
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'database-logging'
        );

        $this->app->register(EventQueryServiceProvider::class);

        $this->app->singleton(LoggingData::class, function() {
            return new LoggingData();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        // deliberately in boot(), not register(): the channel definition has to
        // be injected after every config source has settled, otherwise an
        // application (or a test) overriding database-logging.log would be
        // overwritten by this provider
        $this->registerLogChannel();

        $this->loadMigrationsFrom([self::MIGRATION_PATH]);

        if ($this->app->runningInConsole()) {
            $this->publish();
            $this->commands([
                DatabaseLoggingInstall::class,
                DatabaseLoggingPurge::class
            ]);
        }

        $this->loadRoutesFrom(self::ROUTE_PATH . '/web.php');
        $this->loadViewsFrom(self::VIEW_PATH, 'LaravelDatabaseLogging');

        $this->registerMiddlewareAlias();
    }
}
