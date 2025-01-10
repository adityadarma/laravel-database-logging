<?php

namespace AdityaDarma\LaravelDatabaseLogging\Middleware;

use AdityaDarma\LaravelDatabaseLogging\LoggingData;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        try {
            LoggingData::store($request, $response);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        } finally {
            return $response;
        }
    }
}
