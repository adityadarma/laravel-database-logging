<?php

namespace AdityaDarma\LaravelDatabaseLogging\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use AdityaDarma\LaravelDatabaseLogging\LaravelDatabaseLoggingServiceProvider;

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
     * @return void
     */
    public function handle(): void
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

        $this->line('-----------------------------');

        //migration
        $this->publishMigration();
        $this->info("migration published");

        $this->line('-----------------------------');

        //assets
        if (File::exists(public_path(config('database-logging.assets_path')))) {
            $confirm = $this->confirm("assets file already exist. Do you want to overwrite?");
            if ($confirm) {
                $this->publishAssets();
                $this->info("assets overwrite finished");
            } else {
                $this->info("skipped assets publish");
            }
        } else {
            $this->publishAssets();
            $this->info("assets published");
        }
    }

    private function publishConfig(): void
    {
        $this->call('vendor:publish', [
            '--provider' => LaravelDatabaseLoggingServiceProvider::class,
            '--tag'      => 'config',
            '--force'    => true
        ]);
    }

    private function publishMigration(): void
    {
        $this->call('vendor:publish', [
            '--provider' => LaravelDatabaseLoggingServiceProvider::class,
            '--tag'      => 'migrations',
            '--force'    => true
        ]);
    }

    private function publishAssets(): void
    {
        $this->call('vendor:publish', [
            '--provider' => LaravelDatabaseLoggingServiceProvider::class,
            '--tag'      => 'assets',
            '--force'    => true
        ]);
    }
}
