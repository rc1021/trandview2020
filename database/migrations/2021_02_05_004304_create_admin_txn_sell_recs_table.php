<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTxnSellRecsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_txn_sell_recs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('txn_exit_id');

            $table->dateTime('liquidation_start_at'); // 平倉交易起始日期時間
            $table->dateTime('liquidation_done_at'); // 平倉交易結束日期時間
            $table->integer('liquidation_duration')->default(0); // 平倉交易持續時間
            $table->float('liquidation_price_avg', 24, 8)->default(0); // 平倉價位(均價)
            $table->float('transaction_fee', 24, 8)->default(0); // 交易手續費
            $table->float('gain_funds', 24, 8)->default(0); // 取回資金
            $table->float('profit_and_loss', 24, 8)->default(0); // 損益
            $table->float('profit_and_loss_rate', 24, 8)->default(0); // 損益率(%)
            $table->float('r_value', 24, 8)->default(0); // R值
            $table->float('sell_total_funds', 24, 8)->default(0); // 交易後可交易總資金
            $table->text('response'); // 賣出後取得的資訊
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
        Schema::dropIfExists('admin_txn_sell_recs');
    }
}
