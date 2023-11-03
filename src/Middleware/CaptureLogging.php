<?php

namespace AdityaDarma\LaravelDatabaseLogging\Middleware;

use AdityaDarma\LaravelDatabaseLogging\LoggingData;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureLogging
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        LoggingData::store($request, $response);

        return $response;
    }
}
