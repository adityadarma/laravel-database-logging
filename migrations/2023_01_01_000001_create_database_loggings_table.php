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
        Schema::create('database_loggings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('loggable');
            $table->string('host');
            $table->text('path');
            $table->text('agent')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('method',10);
            $table->longText('data');
            $table->longText('request');
            $table->longText('response');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('database_loggings');
    }
};
