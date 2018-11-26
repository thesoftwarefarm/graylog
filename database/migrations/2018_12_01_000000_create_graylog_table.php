<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGraylogTable extends Migration
{
    public function up()
    {
        Schema::create('graylog', function (Blueprint $table) {
            $table->increments('id');
            $table->string('host')->nullable();
            $table->longText('payload');
            $table->enum('status', ['pending', 'queued'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('graylog');
    }
}