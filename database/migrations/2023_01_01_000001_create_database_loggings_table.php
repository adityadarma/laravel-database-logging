<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatabaseLoggingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('database_loggings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->nullableMorphs('loggable');
            $table->string('host');
            $table->text('path');
            $table->text('agent')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('method',10);
            $table->text('data');
            $table->text('request');
            $table->text('response');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('database_loggings');
    }
}
