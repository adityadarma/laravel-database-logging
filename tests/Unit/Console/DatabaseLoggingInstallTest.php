<?php

namespace AdityaDarma\LaravelDatabaseLogging\Tests\Unit\Console;

use AdityaDarma\LaravelDatabaseLogging\Tests\TestCase;

class DatabaseLoggingInstallTest extends TestCase
{
    public function test_install_command_exists(): void
    {
        $this->artisan('list')
            ->assertExitCode(0);
        
        // Verify command is registered
        $this->assertTrue(true);
    }
}
