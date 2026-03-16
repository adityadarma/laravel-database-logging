<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Unit\ServiceProviders;

use AdityaDarma\LaravelDatabaseLogging\LaravelDatabaseLoggingServiceProvider;
use AdityaDarma\LaravelDatabaseLogging\LoggingData;
use AdityaDarma\LaravelDatabaseLogging\Tests\TestCase;

class LaravelDatabaseLoggingServiceProviderTest extends TestCase
{
    public function test_service_provider_is_registered(): void
    {
        $providers = $this->app->getLoadedProviders();
        
        $this->assertArrayHasKey(LaravelDatabaseLoggingServiceProvider::class, $providers);
    }

    public function test_config_is_published(): void
    {
        $this->assertNotNull(config('database-logging'));
        $this->assertIsArray(config('database-logging'));
    }

    public function test_logging_data_is_singleton(): void
    {
        $instance1 = $this->app->make(LoggingData::class);
        $instance2 = $this->app->make(LoggingData::class);

        $this->assertSame($instance1, $instance2);
    }

    public function test_middleware_is_registered(): void
    {
        $router = $this->app->make('router');
        $middleware = $router->getMiddleware();

        $this->assertArrayHasKey('capture-logging', $middleware);
    }

    public function test_routes_are_loaded(): void
    {
        $routes = $this->app->make('router')->getRoutes();
        
        $routeNames = [];
        foreach ($routes as $route) {
            if ($route->getName()) {
                $routeNames[] = $route->getName();
            }
        }

        $this->assertContains('database-logging.index', $routeNames);
        $this->assertContains('database-logging.data', $routeNames);
        $this->assertContains('database-logging.show', $routeNames);
        $this->assertContains('database-logging.destroy', $routeNames);
    }

    public function test_views_are_loaded(): void
    {
        $this->assertTrue(view()->exists('LaravelDatabaseLogging::index'));
        $this->assertTrue(view()->exists('LaravelDatabaseLogging::table'));
    }
}
