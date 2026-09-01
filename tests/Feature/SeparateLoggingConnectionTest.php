<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Feature;

use AdityaDarma\LaravelDatabaseLogging\LaravelDatabaseLoggingServiceProvider;
use AdityaDarma\LaravelDatabaseLogging\LoggingData;
use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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

        // routes are registered at boot, so the middleware has to be relaxed
        // here rather than inside the test body
        $app['config']->set('database-logging.middleware', []);
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
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

    /**
     * Rows written before user_name existed have to fall back to the related
     * record, which lives in the *other* database. MorphTo cannot do this:
     * createModelByType() pushes the related model onto the parent connection
     * whenever it declares none, so the lookup used to run as
     * "select * from users" against the logging database.
     */
    public function test_name_falls_back_to_the_other_connection_when_snapshot_is_null(): void
    {
        $user = SeparateConnectionUser::on('main')->create([
            'name' => 'Aditya Darma',
            'email' => 'a@b.c',
            'password' => 'secret',
        ]);

        DatabaseLogging::query()->insert([
            'loggable_type' => SeparateConnectionUser::class,
            'loggable_id' => $user->getKey(),
            'user_name' => null,
            'host' => 'http://localhost',
            'path' => 'orders',
            'method' => 'POST',
            'data' => '[]',
            'request' => '[]',
            'response' => '[]',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DatabaseLogging::flushLoggableCache();

        $log = DatabaseLogging::query()->firstOrFail();

        $this->assertNull($log->getRawOriginal('user_name'));
        $this->assertSame('Aditya Darma', $log->name);
    }

    public function test_resolved_actor_is_only_fetched_once_per_page(): void
    {
        $user = SeparateConnectionUser::on('main')->create([
            'name' => 'Aditya Darma',
            'email' => 'a@b.c',
            'password' => 'secret',
        ]);

        foreach (range(1, 5) as $i) {
            DatabaseLogging::query()->insert([
                'loggable_type' => SeparateConnectionUser::class,
                'loggable_id' => $user->getKey(),
                'host' => 'http://localhost',
                'path' => "orders/$i",
                'method' => 'POST',
                'data' => '[]',
                'request' => '[]',
                'response' => '[]',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DatabaseLogging::flushLoggableCache();

        // Connection::listen() registers on the shared dispatcher, so the
        // connection has to be filtered explicitly
        $selects = 0;
        DB::listen(function ($query) use (&$selects) {
            if ($query->connectionName === 'main'
                && str_starts_with(strtolower(trim($query->sql)), 'select')) {
                $selects++;
            }
        });

        foreach (DatabaseLogging::query()->get() as $log) {
            $this->assertSame('Aditya Darma', $log->name);
        }

        $this->assertSame(1, $selects, 'the actor should be fetched once, not once per row');
    }

    public function test_viewer_pages_do_not_query_the_logging_database_for_users(): void
    {
        $user = SeparateConnectionUser::on('main')->create([
            'name' => 'Aditya Darma',
            'email' => 'a@b.c',
            'password' => 'secret',
        ]);

        $this->actingAs($user);

        $request = Request::create('/orders', 'POST', ['foo' => 'bar']);
        $request->setUserResolver(fn () => $user);

        LoggingData::reset();
        LoggingData::request($request);
        LoggingData::store($request, new Response('ok', 200));

        DatabaseLogging::flushLoggableCache();

        $this->get(config('database-logging.route_path'))->assertOk();
        $this->getJson(config('database-logging.route_path') . '/datatable?draw=1&start=0&length=10')
            ->assertOk()
            ->assertJsonPath('data.0.user', 'Aditya Darma');
    }
}
