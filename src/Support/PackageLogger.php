<?php

namespace AdityaDarma\LaravelDatabaseLogging\Support;

use Illuminate\Log\LogManager;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class PackageLogger
{
    /**
     * Resolve the logger the package writes its own failures to.
     *
     * Falls back to the application default channel when no dedicated channel
     * is configured, or when resolving the configured one fails. Logging must
     * never be the thing that breaks a request.
     *
     * @return LoggerInterface
     */
    public static function resolve(): LoggerInterface
    {
        $channel = config('database-logging.log.channel');

        if (empty($channel)) {
            return Log::getFacadeRoot();
        }

        try {
            $manager = Log::getFacadeRoot();

            return $manager instanceof LogManager
                ? $manager->channel($channel)
                : $manager;
        } catch (Throwable) {
            return Log::getFacadeRoot();
        }
    }

    /**
     * Record a package level failure.
     *
     * @param Throwable $e
     * @return void
     */
    public static function error(Throwable $e): void
    {
        try {
            self::resolve()->error($e->getMessage(), ['exception' => $e]);
        } catch (Throwable) {
            // swallowed on purpose: the package must not turn a logging
            // failure into a second, louder failure
        }
    }
}
