<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Helpers;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use Illuminate\Http\Request;

class TestHelper
{
    /**
     * Create a sample database logging record
     */
    public static function createSampleLogging(array $attributes = []): DatabaseLogging
    {
        return DatabaseLogging::create(array_merge([
            'method' => 'GET',
            'host' => 'http://localhost',
            'path' => '/test',
            'ip_address' => '127.0.0.1',
            'agent' => 'Mozilla/5.0 (Test Agent)',
            'data' => [],
            'request' => [],
            'response' => [],
        ], $attributes));
    }

    /**
     * Create multiple sample logging records
     */
    public static function createMultipleLoggings(int $count = 5): void
    {
        for ($i = 0; $i < $count; $i++) {
            self::createSampleLogging([
                'path' => "/test-{$i}",
                'method' => $i % 2 === 0 ? 'GET' : 'POST',
            ]);
        }
    }

    /**
     * Create a mock request
     */
    public static function createMockRequest(
        string $method = 'GET',
        string $uri = '/test',
        array $parameters = []
    ): Request {
        $request = Request::create($uri, $method, $parameters);
        $request->headers->set('User-Agent', 'TestAgent/1.0');
        $request->headers->set('Accept', 'application/json');
        
        return $request;
    }

    /**
     * Create a logging with query data
     */
    public static function createLoggingWithQuery(array $queries = []): DatabaseLogging
    {
        if (empty($queries)) {
            $queries = [
                ['query' => 'SELECT * FROM users', 'time' => 50],
                ['query' => 'SELECT * FROM posts WHERE user_id = 1', 'time' => 75],
            ];
        }

        return self::createSampleLogging([
            'data' => $queries,
        ]);
    }

    /**
     * Create a logging with full request/response data
     */
    public static function createFullLogging(): DatabaseLogging
    {
        return self::createSampleLogging([
            'method' => 'POST',
            'host' => 'http://localhost',
            'path' => '/api/users',
            'ip_address' => '192.168.1.100',
            'agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'loggable_id' => 1,
            'loggable_type' => 'App\\Models\\User',
            'data' => [
                ['query' => 'INSERT INTO users (name, email) VALUES (?, ?)', 'time' => 25],
                ['query' => 'SELECT * FROM users WHERE id = 1', 'time' => 15],
            ],
            'request' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role' => 'admin',
            ],
            'response' => [
                'status' => 'success',
                'data' => [
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
            ],
        ]);
    }

    /**
     * Assert logging has required fields
     */
    public static function assertLoggingHasRequiredFields(DatabaseLogging $logging): void
    {
        \PHPUnit\Framework\Assert::assertNotNull($logging->method);
        \PHPUnit\Framework\Assert::assertNotNull($logging->host);
        \PHPUnit\Framework\Assert::assertNotNull($logging->path);
    }

    /**
     * Get sample query data
     */
    public static function getSampleQueries(): array
    {
        return [
            ['query' => 'SELECT * FROM users WHERE id = 1', 'time' => 50],
            ['query' => 'UPDATE users SET name = "John" WHERE id = 1', 'time' => 75],
            ['query' => 'DELETE FROM sessions WHERE expired_at < NOW()', 'time' => 100],
        ];
    }

    /**
     * Get sample request headers
     */
    public static function getSampleHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'Mozilla/5.0',
            'X-Requested-With' => 'XMLHttpRequest',
        ];
    }
}
