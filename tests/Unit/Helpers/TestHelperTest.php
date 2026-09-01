<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Unit\Helpers;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use AdityaDarma\LaravelDatabaseLogging\Tests\Helpers\TestHelper;
use AdityaDarma\LaravelDatabaseLogging\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TestHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_sample_logging(): void
    {
        $logging = TestHelper::createSampleLogging();

        $this->assertInstanceOf(DatabaseLogging::class, $logging);
        $this->assertEquals('GET', $logging->method);
        $this->assertEquals('/test', $logging->path);
    }

    public function test_can_create_sample_logging_with_custom_attributes(): void
    {
        $logging = TestHelper::createSampleLogging([
            'method' => 'POST',
            'path' => '/custom',
        ]);

        $this->assertEquals('POST', $logging->method);
        $this->assertEquals('/custom', $logging->path);
    }

    public function test_can_create_multiple_loggings(): void
    {
        TestHelper::createMultipleLoggings(3);

        $this->assertDatabaseCount('database_loggings', 3);
    }

    public function test_can_create_mock_request(): void
    {
        $request = TestHelper::createMockRequest('POST', '/api/test', ['key' => 'value']);

        $this->assertEquals('POST', $request->method());
        // Request::path() is always returned without a leading slash
        $this->assertEquals('api/test', $request->path());
        $this->assertEquals('value', $request->input('key'));
    }

    public function test_can_create_logging_with_query(): void
    {
        $logging = TestHelper::createLoggingWithQuery();

        $this->assertIsArray($logging->data);
        $this->assertNotEmpty($logging->data);
    }

    public function test_can_create_full_logging(): void
    {
        $logging = TestHelper::createFullLogging();

        TestHelper::assertLoggingHasRequiredFields($logging);
        $this->assertIsArray($logging->data);
        $this->assertIsArray($logging->request);
        $this->assertIsArray($logging->response);
    }

    public function test_get_sample_queries_returns_array(): void
    {
        $queries = TestHelper::getSampleQueries();

        $this->assertIsArray($queries);
        $this->assertNotEmpty($queries);
        $this->assertArrayHasKey('query', $queries[0]);
        $this->assertArrayHasKey('time', $queries[0]);
    }

    public function test_get_sample_headers_returns_array(): void
    {
        $headers = TestHelper::getSampleHeaders();

        $this->assertIsArray($headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('Accept', $headers);
    }
}
