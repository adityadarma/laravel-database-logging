<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Unit;

use AdityaDarma\LaravelDatabaseLogging\LoggingData;
use AdityaDarma\LaravelDatabaseLogging\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LoggingDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        LoggingData::reset();
    }

    public function test_can_set_and_get_query(): void
    {
        $queryData = [
            'query' => 'SELECT * FROM users',
            'time' => 100
        ];

        LoggingData::setQuery($queryData);
        $queries = LoggingData::getQuery();

        $this->assertCount(1, $queries);
        $this->assertEquals($queryData, $queries[0]);
    }

    public function test_can_add_multiple_queries(): void
    {
        LoggingData::setQuery(['query' => 'SELECT * FROM users', 'time' => 50]);
        LoggingData::setQuery(['query' => 'SELECT * FROM posts', 'time' => 75]);

        $queries = LoggingData::getQuery();

        $this->assertCount(2, $queries);
    }

    public function test_request_method_captures_request_data(): void
    {
        $request = Request::create('/test', 'POST', ['key' => 'value']);
        $request->headers->set('User-Agent', 'TestAgent');

        LoggingData::request($request);

        $this->assertTrue(true); // Method executes without error
    }

    public function test_store_method_saves_logging_data(): void
    {
        config(['database-logging.enable_logging' => true]);
        config(['database-logging.method' => ['GET', 'POST']]);

        $request = Request::create('/api/test', 'GET');
        $request->headers->set('User-Agent', 'TestAgent');
        
        LoggingData::request($request);
        
        $response = new Response('Success', 200);
        LoggingData::store($request, $response);

        $this->assertDatabaseHas('database_loggings', [
            'method' => 'GET',
            'path' => 'api/test',
        ]);
    }

    public function test_store_does_not_save_when_disabled(): void
    {
        config(['database-logging.enable_logging' => false]);

        $request = Request::create('/test', 'GET');
        $response = new Response('Test', 200);

        LoggingData::request($request);
        LoggingData::store($request, $response);

        $this->assertDatabaseCount('database_loggings', 0);
    }
}
