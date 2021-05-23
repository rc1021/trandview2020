<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTxnSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_txn_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');

            $table->float('initial_tradable_total_funds', 24, 8)->default(1); // 初始可交易總資金%
            $table->float('initial_capital_risk', 24, 8)->default(0.1); // 初始資金風險
            $table->tinyInteger('lever_switch')->default(1); // 槓桿開關
            $table->float('btn_daily_interest', 24, 8)->default(0.0003); // 標的幣借款利息(24h)
            $table->float('usdt_daily_interest', 24, 8)->default(0.0015); // 計價幣借款利息(24h)

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
        Schema::dropIfExists('admin_txn_settings');
    }
}
