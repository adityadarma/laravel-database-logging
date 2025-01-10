<?php

namespace AdityaDarma\LaravelDatabaseLogging\Console;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use Illuminate\Console\Command;

class DatabaseLoggingPurge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database-logging:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It will remove database logging';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $duration = config('database-logging.duration');
        if ($duration) {
            DatabaseLogging::whereDate('created_at', '<=', now()->subDays($duration)->format('Y-m-d'))
                ->delete();
        }
    }
}
