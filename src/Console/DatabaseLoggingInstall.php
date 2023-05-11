<?php

namespace AdityaDarma\LaravelDatabaseLogging\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DatabaseLoggingInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database-logging:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It will publish config file and run a migration for database logging';

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
     * @return mixed
     */
    public function handle()
    {
        //config
        if (File::exists(config_path('database-logging.php'))) {
            $confirm = $this->confirm("database-logging.php config file already exist. Do you want to overwrite?");
            if ($confirm) {
                $this->publishConfig();
                $this->info("config overwrite finished");
            } else {
                $this->info("skipped config publish");
            }
        } else {
            $this->publishConfig();
            $this->info("config published");
        }


        //migration
        $migrationFile = "2023_01_01_000001_create_database_logging_table.php";
        if (File::exists(database_path("migrations/$migrationFile"))) {
            $confirm = $this->confirm("migration file already exist. Do you want to overwrite?");
            if ($confirm) {
                $this->publishMigration();
                $this->info("migration overwrite finished");
            } else {
                $this->info("skipped migration publish");
            }
        } else {
            $this->publishMigration();
            $this->info("migration published");
        }

        $this->line('-----------------------------');
        if (!Schema::hasTable('database_loggings')) {
            $this->call('migrate');
        } else {
            $this->error('logs table already exist in your database. migration not run successfully');
        }

    }

    private function publishConfig()
    {
        $this->call('vendor:publish', [
            '--provider' => "AdityaDarma\LaravelDatabaseLogging\LaravelDatabaseLoggingServiceProvider",
            '--tag'      => 'config',
            '--force'    => true
        ]);
    }

    private function publishMigration()
    {
        $this->call('vendor:publish', [
            '--provider' => "AdityaDarma\LaravelDatabaseLogging\LaravelDatabaseLoggingServiceProvider",
            '--tag'      => 'migrations',
            '--force'    => true
        ]);
    }
}
