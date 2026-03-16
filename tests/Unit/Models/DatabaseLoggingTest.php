<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Unit\Models;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use AdityaDarma\LaravelDatabaseLogging\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabaseLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_database_logging(): void
    {
        $logging = DatabaseLogging::create([
            'method' => 'GET',
            'host' => 'http://localhost',
            'path' => '/test',
            'ip_address' => '127.0.0.1',
            'agent' => 'TestAgent',
            'loggable_id' => 1,
            'loggable_type' => 'App\\Models\\User',
            'user_name' => 'Test User',
            'data' => [],
            'request' => [],
            'response' => [],
        ]);

        $this->assertDatabaseHas('database_loggings', [
            'method' => 'GET',
            'host' => 'http://localhost',
            'path' => '/test',
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertInstanceOf(DatabaseLogging::class, $logging);
    }

    public function test_fillable_attributes(): void
    {
        $model = new DatabaseLogging();
        $fillable = $model->getFillable();

        $this->assertContains('method', $fillable);
        $this->assertContains('host', $fillable);
        $this->assertContains('path', $fillable);
        $this->assertContains('ip_address', $fillable);
        $this->assertContains('agent', $fillable);
        $this->assertContains('loggable_id', $fillable);
        $this->assertContains('loggable_type', $fillable);
        $this->assertContains('user_name', $fillable);
    }

    public function test_casts_attributes(): void
    {
        $logging = DatabaseLogging::create([
            'method' => 'POST',
            'host' => 'http://localhost',
            'path' => '/api/test',
            'ip_address' => '192.168.1.1',
            'agent' => 'TestAgent',
            'data' => [['query' => 'SELECT * FROM users', 'time' => 50]],
            'request' => ['key' => 'value'],
            'response' => ['status' => 'success'],
            'query' => ['SELECT * FROM users'],
        ]);

        $this->assertIsArray($logging->data);
        $this->assertIsArray($logging->request);
        $this->assertIsArray($logging->response);
        $this->assertIsArray($logging->query);
    }
}
