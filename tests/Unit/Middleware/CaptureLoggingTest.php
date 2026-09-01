<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Unit\Middleware;

use AdityaDarma\LaravelDatabaseLogging\LoggingData;
use AdityaDarma\LaravelDatabaseLogging\Middleware\CaptureLogging;
use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use AdityaDarma\LaravelDatabaseLogging\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CaptureLoggingTest extends TestCase
{
    use RefreshDatabase;

    private function handle(Request $request, ?callable $duringRequest = null): Response
    {
        return (new CaptureLogging())->handle($request, function () use ($duringRequest) {
            if ($duringRequest) {
                $duringRequest();
            }

            return new Response('ok', 200);
        });
    }

    /**
     * On a long running runtime the static arrays survive between requests, so
     * without an explicit reset request B would inherit request A's payload.
     */
    public function test_state_does_not_leak_between_requests(): void
    {
        $this->handle(
            Request::create('/first', 'POST', ['field' => 'first-request']),
            fn () => LoggingData::setData(['table' => 'orders', 'id' => 1, 'event' => 'create'])
        );

        $this->handle(Request::create('/second', 'POST', ['field' => 'second-request']));

        $logs = DatabaseLogging::query()->orderBy('id')->get();

        $this->assertCount(2, $logs);

        $this->assertSame(['field' => 'first-request'], $logs[0]->request);
        $this->assertCount(1, $logs[0]->data);

        $this->assertSame(['field' => 'second-request'], $logs[1]->request);
        $this->assertSame([], $logs[1]->data);
    }

    public function test_static_state_is_cleared_after_the_response(): void
    {
        $this->handle(
            Request::create('/orders', 'POST'),
            fn () => LoggingData::setData(['table' => 'orders', 'id' => 1, 'event' => 'create'])
        );

        $this->assertSame([], LoggingData::getData());
        $this->assertSame([], LoggingData::getQuery());
    }

    public function test_non_success_response_is_not_logged(): void
    {
        (new CaptureLogging())->handle(
            Request::create('/orders', 'POST'),
            fn () => new Response('nope', 422)
        );

        $this->assertSame(0, DatabaseLogging::query()->count());
    }
}
