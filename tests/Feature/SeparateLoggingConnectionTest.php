<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Feature;

use AdityaDarma\LaravelDatabaseLogging\LaravelDatabaseLoggingServiceProvider;
use AdityaDarma\LaravelDatabaseLogging\LoggingData;
use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class SeparateConnectionUser extends Authenticatable
{
    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}

class SeparateLoggingConnectionTest extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [LaravelDatabaseLoggingServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // main app database
        $app['config']->set('database.default', 'main');
        $app['config']->set('database.connections.main', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // separate logging database (only holds database_loggings)
        $app['config']->set('database.connections.logging', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('database-logging.connection_logging', 'logging');
        $app['config']->set('database-logging.enable_logging', true);
        $app['config']->set('database-logging.model', [
            SeparateConnectionUser::class => 'name',
        ]);
        $app['config']->set('auth.providers.users.model', SeparateConnectionUser::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::connection('main')->create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
        });

        // package migrations must land on the logging connection
        $this->artisan('migrate', ['--path' => __DIR__ . '/../../database/migrations', '--realpath' => true])
            ->assertSuccessful();
    }

    public function test_migrations_run_on_the_logging_connection(): void
    {
        $this->assertTrue(
            Schema::connection('logging')->hasTable('database_loggings'),
            'database_loggings should exist on the logging connection'
        );
        $this->assertFalse(
            Schema::connection('main')->hasTable('database_loggings'),
            'database_loggings should NOT be created on the default connection'
        );
        $this->assertTrue(Schema::connection('logging')->hasColumn('database_loggings', 'user_name'));
        $this->assertTrue(Schema::connection('logging')->hasColumn('database_loggings', 'query'));
    }

    public function test_log_is_written_to_logging_connection_and_name_is_resolved(): void
    {
        $user = SeparateConnectionUser::on('main')->create([
            'name' => 'Aditya Darma',
            'email' => 'a@b.c',
            'password' => 'secret',
        ]);

        $this->actingAs($user);

        LoggingData::reset();

        $request = Request::create('/orders', 'POST', ['foo' => 'bar']);
        $request->setUserResolver(fn () => $user);

        LoggingData::request($request);
        LoggingData::store($request, new Response('ok', 200));

        $log = DatabaseLogging::query()->first();

        $this->assertNotNull($log, 'log row was not created');
        $this->assertSame('logging', $log->getConnectionName());
        $this->assertSame((string) $user->getKey(), (string) $log->loggable_id);

        // this is the thing that breaks with a separate logging DB
        $this->assertSame('Aditya Darma', $log->name);
    }
}
