<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeySecretsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('key_secrets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('alias'); // key名稱
            $table->string('type'); // PerformanceApiType
            $table->string('key');
            $table->string('secret');
            $table->timestamps();

            $table->unique(['user_id', 'alias']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('key_secrets');
    }
}
