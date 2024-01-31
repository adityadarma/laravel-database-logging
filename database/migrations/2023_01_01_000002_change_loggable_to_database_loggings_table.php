<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('database_loggings', function (Blueprint $table) {
            $table->string('loggable_type')->nullable()->change();
            $table->unsignedBigInteger('loggable_id')->nullable()->change();
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
            $table->string('loggable_type')->change();
            $table->unsignedBigInteger('loggable_id')->change();
        });
    }
};
