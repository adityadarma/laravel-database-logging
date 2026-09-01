<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Unit\Controllers;

use AdityaDarma\LaravelDatabaseLogging\Controllers\DatabaseLoggingController;
use AdityaDarma\LaravelDatabaseLogging\Tests\Helpers\TestHelper;
use AdityaDarma\LaravelDatabaseLogging\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;

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

    /**
     * The sqlite suite never touches the MySQL branch, where SHOW TABLES
     * returns a single column named after the database and the value has to be
     * read positionally.
     */
    public function test_positional_row_reading_handles_show_tables_output(): void
    {
        $rows = [
            (object) ['Tables_in_prod' => 'users'],
            (object) ['Tables_in_prod' => 'database_loggings'],
        ];

        $this->assertSame(
            ['users', 'database_loggings'],
            $this->pluckNames($rows)
        );
    }

    public function test_keyed_row_reading_handles_the_other_drivers(): void
    {
        $this->assertSame(
            ['public_users'],
            $this->pluckNames([(object) ['tablename' => 'public_users']], 'tablename')
        );

        $this->assertSame(
            ['dbo_users'],
            $this->pluckNames([(object) ['TABLE_NAME' => 'dbo_users']], 'TABLE_NAME')
        );
    }

    public function test_rows_without_a_usable_name_are_dropped(): void
    {
        $this->assertSame(
            ['users'],
            $this->pluckNames([
                (object) ['name' => 'users'],
                (object) ['name' => null],
                (object) ['name' => ''],
                (object) [],
            ], 'name')
        );
    }

    private function pluckNames(array $rows, ?string $key = null): array
    {
        $controller = new DatabaseLoggingController();

        $method = new ReflectionMethod($controller, 'pluckNames');
        $method->setAccessible(true);

        return $method->invoke($controller, $rows, $key);
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
