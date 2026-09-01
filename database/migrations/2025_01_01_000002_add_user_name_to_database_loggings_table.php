<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migration on the configured logging connection.
     *
     * @return string|null
     */
    public function getConnection(): ?string
    {
        return config('database-logging.connection_logging');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('database_loggings', function (Blueprint $table) {
            $table->string('user_name')->nullable()->after('loggable_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('database_loggings', function (Blueprint $table) {
            $table->dropColumn('user_name');
        });
    }
};
