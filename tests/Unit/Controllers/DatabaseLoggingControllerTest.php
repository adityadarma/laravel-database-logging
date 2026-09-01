<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Unit\Controllers;

use AdityaDarma\LaravelDatabaseLogging\Tests\Helpers\TestHelper;
use AdityaDarma\LaravelDatabaseLogging\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabaseLoggingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        // the middleware defaults to web+auth, which would redirect us away
        $app['config']->set('database-logging.middleware', []);
    }

    public function test_index_lists_tables_of_a_custom_named_connection(): void
    {
        // a connection named anything other than its driver used to hit
        // "Database driver tidak didukung."
        config()->set('database.connections.primary', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $response = $this->get(config('database-logging.route_path'));

        $response->assertOk();
        $response->assertViewHas('tables');
        $this->assertArrayHasKey('database_loggings', $response->viewData('tables'));
    }

    public function test_datatable_works_without_a_search_parameter(): void
    {
        TestHelper::createMultipleLoggings(3);

        $response = $this->getJson(config('database-logging.route_path') . '/datatable?draw=1&start=0&length=10');

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 3);
        $response->assertJsonPath('recordsFiltered', 3);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_datatable_filters_by_search_value(): void
    {
        TestHelper::createSampleLogging([
            'data' => [['table' => 'orders', 'id' => 1, 'event' => 'create', 'data' => []]],
        ]);
        TestHelper::createSampleLogging([
            'data' => [['table' => 'invoices', 'id' => 2, 'event' => 'update', 'data' => []]],
        ]);

        $response = $this->getJson(
            config('database-logging.route_path') . '/datatable?draw=1&start=0&length=10&search[value]=orders'
        );

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 2);
        $response->assertJsonPath('recordsFiltered', 1);
    }
}
