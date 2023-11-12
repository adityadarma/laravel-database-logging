<?php

namespace AdityaDarma\LaravelDatabaseLogging\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $duration = config('database-logging.duration');
        if ($duration) {
            DB::table('database_loggings')
                ->when($duration, function ($query) use ($duration) {
                    $query->whereDate('created_at', '<=', now()->subDays($duration)->format('Y-m-d'));
                })
                ->delete();
        }
    }
}
