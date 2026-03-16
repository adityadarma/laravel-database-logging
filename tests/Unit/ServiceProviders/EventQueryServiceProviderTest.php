<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Unit\ServiceProviders;

use AdityaDarma\LaravelDatabaseLogging\EventQueryServiceProvider;
use AdityaDarma\LaravelDatabaseLogging\Listeners\QueryListener;
use AdityaDarma\LaravelDatabaseLogging\Tests\TestCase;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;

class EventQueryServiceProviderTest extends TestCase
{
    public function test_event_service_provider_is_registered(): void
    {
        $providers = $this->app->getLoadedProviders();
        
        $this->assertArrayHasKey(EventQueryServiceProvider::class, $providers);
    }

    public function test_query_executed_event_has_listener(): void
    {
        Event::fake();
        
        $this->assertTrue(Event::hasListeners(QueryExecuted::class));
    }
}
