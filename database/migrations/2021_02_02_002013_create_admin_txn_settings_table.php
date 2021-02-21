<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use BinanceApi\Enums\SymbolType;

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

            $table->float('initial_tradable_total_funds', 24, 8)->default(0); // 初始可交易總資金
            $table->string('transaction_matching')->default(SymbolType::BTCUSDT); // 交易配對
            $table->float('initial_capital_risk', 24, 8)->default(0); // 初始資金風險
            $table->tinyInteger('lever_switch')->default(0); // 槓桿開關
            $table->float('transaction_fees', 24, 8)->default(0.075); // 交易手續費%
            $table->tinyInteger('prededuct_handling_fee')->default(1); // 預先扣除手續費(開/關)

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
