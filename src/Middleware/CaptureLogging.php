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
        LoggingData::request($request);

        $response = $next($request);

        LoggingData::store($request, $response);

        return $response;
    }
}
