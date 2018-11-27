<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGraylogTable extends Migration
{
    public function up()
    {
        Schema::create('graylog', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('host')->nullable()->index();
            $table->longText('payload');
            $table->enum('status', ['pending', 'queued', 'failed', 'sent'])->default('pending')->index();
            $table->integer('retries')->default(0)->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('graylog');
    }
}