<?php

namespace AdityaDarma\LaravelDatabaseLogging\Middleware;

use AdityaDarma\LaravelDatabaseLogging\LoggingData;
use Closure;
use Illuminate\Http\Request;
use JsonException;
use Symfony\Component\HttpFoundation\Response;

class CaptureLogging
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     * @throws JsonException
     */
    public function handle(Request $request, Closure $next): Response
    {
        // LoggingData keeps its state in static properties. On a long running
        // runtime (Octane, RoadRunner, queue workers) those survive between
        // requests, so without an explicit reset the previous request's
        // queries and payload would leak into this log entry.
        LoggingData::reset();

        LoggingData::request($request);

        $response = $next($request);

        try {
            LoggingData::store($request, $response);
        } finally {
            LoggingData::reset();
        }

        return $response;
    }
}
