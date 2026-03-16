<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Unit\Traits;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use AdityaDarma\LaravelDatabaseLogging\Tests\TestCase;
use AdityaDarma\LaravelDatabaseLogging\Traits\DatabaseLoggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabaseLoggableTest extends TestCase
{
    use RefreshDatabase;

    public function test_trait_provides_logs_relationship(): void
    {
        $model = new class extends Model {
            use DatabaseLoggable;
            protected $table = 'users';
        };

        $this->assertTrue(method_exists($model, 'logs'));
    }

    public function test_logs_relationship_returns_correct_type(): void
    {
        $model = new class extends Model {
            use DatabaseLoggable;
            protected $table = 'users';
            public $id = 1;
        };

        $relation = $model->logs();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    }
}
