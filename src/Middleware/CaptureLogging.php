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
        $response = $next($request);

        try {
            LoggingData::store($request, $response);
        } finally {
            return $response;
        }
    }
}
