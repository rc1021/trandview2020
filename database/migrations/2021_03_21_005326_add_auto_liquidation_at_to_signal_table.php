<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoLiquidationAtToSignalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('signal_histories', function (Blueprint $table) {
            $table->dateTime('auto_liquidation_at', 0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('signal_histories', function (Blueprint $table) {
            $table->dropColumn(['auto_liquidation_at']);
        });
    }
}
