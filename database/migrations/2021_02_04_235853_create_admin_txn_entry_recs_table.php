<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TxnDirectType;
use BinanceApi\Enums\SymbolType;

class CreateAdminTxnEntryRecsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_txn_entry_recs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('signal_history_id');

            $table->dateTime('position_at', 0); // 應開倉日期時間
            $table->float('avaiable_total_funds', 24, 8)->default(0); // 交易前可交易總資金
            $table->tinyInteger('tranding_long_short')->default(TxnDirectType::SHORT); // 交易方向(多/空)
            $table->float('funds_risk', 24, 8)->default(0); // 資金風險(%)
            $table->string('transaction_matching')->default(SymbolType::BTCUSDT); // 交易配對
            $table->tinyInteger('leverage')->default(0); // 槓桿使用
            $table->tinyInteger('prededuct_handling_fee')->default(0); // 預先扣除手續費
            $table->float('transaction_fee', 24, 8)->default(0); // 交易手續費
            $table->float('risk_start_price', 24, 8)->default(0); // 起始風險價位(止損價位)
            $table->float('hight_position_price', 24, 8)->default(0); // 開倉價格容差(最高價位)
            $table->float('low_position_price', 24, 8)->default(0); // 開倉價格容差(最低價位)
            $table->float('entry_price', 24, 8)->default(0); // Entry訊號價位(當時的價位)
            $table->float('funds_risk_amount', 24, 8)->default(0); // 資金風險金額
            $table->float('risk_start', 24, 8)->default(0); // 起始風險%(1R)
            $table->float('position_price', 24, 8)->default(0); // 應開倉部位大小(未加上槓桿量)
            $table->float('leverage_power', 24, 8)->default(0); // 應使用槓桿(倍數)
            $table->float('leverage_price', 24, 8)->default(0); // 應使用槓桿(金額)
            $table->float('leverage_position_price', 24, 8)->default(0); // 應開倉部位大小(加上槓桿量)
            $table->float('position_few', 24, 8)->default(0); // 應開倉(幾口)
            $table->float('tranding_fee_amount', 24, 8)->default(0); // 交易手續費
            $table->float('position_few_amount', 24, 8)->default(0); // 應開倉(預先扣除手續費)
            $table->float('position_price_with_fee', 24, 8)->default(0); // 應開倉部位大小(預先扣除手續費)
            $table->float('leverage_price_with_fee', 24, 8)->default(0); // 應使用槓桿(金額)(預先扣除手續費)
            $table->float('leverage_power_with_fee', 24, 8)->default(0); // 應使用槓桿(倍數)(預先扣除手續費)
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
        Schema::dropIfExists('admin_txn_entry_recs');
    }
}
