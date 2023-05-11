<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatabaseLoggingTable extends Migration
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
            $table->morphs('loggable');
            $table->nullableMorphs('created_by');
            $table->text('url');
            $table->text('agent')->nullable();
            $table->text('ip_address')->nullable();
            $table->string('log_type',50);
            $table->longText('data');
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
