<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Unit\Traits;

use AdityaDarma\LaravelDatabaseLogging\LoggingData;
use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use AdityaDarma\LaravelDatabaseLogging\Tests\TestCase;
use AdityaDarma\LaravelDatabaseLogging\Traits\DatabaseLoggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class LoggableUser extends Model
{
    use DatabaseLoggable;

    protected $table = 'loggable_users';

    protected $guarded = [];

    public $timestamps = false;
}

class DatabaseLoggableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('loggable_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('password')->nullable();
        });
    }

    public function test_trait_provides_logs_relationship(): void
    {
        $this->assertTrue(method_exists(new LoggableUser(), 'logs'));
    }

    public function test_logs_relationship_returns_correct_type(): void
    {
        // a log row points back through loggable_type/loggable_id, so the
        // relation is polymorphic: MorphMany, not HasMany
        $this->assertInstanceOf(MorphMany::class, (new LoggableUser())->logs());
    }

    public function test_logs_relationship_resolves_records(): void
    {
        $user = LoggableUser::create(['name' => 'Aditya']);

        DatabaseLogging::create([
            'method' => 'POST',
            'host' => 'http://localhost',
            'path' => 'orders',
            'loggable_type' => LoggableUser::class,
            'loggable_id' => $user->getKey(),
            'data' => [],
            'request' => [],
            'response' => [],
        ]);

        $this->assertCount(1, $user->logs()->get());
    }

    public function test_excluded_columns_are_not_recorded(): void
    {
        // exclude_column_query_logging is read when the event fires, so it can
        // be set here; log_events.create is read at model boot and already
        // defaults to true in the package config
        config(['database-logging.exclude_column_query_logging' => ['password']]);

        LoggingData::reset();
        LoggableUser::create(['name' => 'Aditya', 'password' => 'super-secret']);

        $recorded = json_encode(LoggingData::getData());

        $this->assertStringContainsString('name', $recorded);
        $this->assertStringNotContainsString('password', $recorded);
        $this->assertStringNotContainsString('super-secret', $recorded);
    }
}
