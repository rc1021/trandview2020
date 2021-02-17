<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTxnBuyRecsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_txn_buy_recs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('txn_entry_id');

            $table->dateTime('position_start_at'); // 開倉交易起始日期時間
            $table->dateTime('position_done_at'); // 開倉交易完成日期時間
            $table->integer('position_duration')->default(0); // 開倉交易持續時間
            $table->float('position_price', 24, 8)->default(0); // 實際開倉部位大小
            $table->float('position_price_avg', 24, 8)->default(0); // 實際開倉價位(均價)
            $table->float('position_quota', 24, 8)->default(0); // 實際開倉(幾口)
            $table->float('leverage_power', 24, 8)->default(0); // 實際使用槓桿(倍數)
            $table->float('leverage_price', 24, 8)->default(0); // 實際使用槓桿(金額)
            $table->float('risk_start', 24, 8)->default(0); // 實際起始風險%(1R)
            $table->float('transaction_fee', 24, 8)->default(0); // 交易手續費(幾口)
            $table->float('funds_risk', 24, 8)->default(0); // 實際資金風險(金額)
            $table->float('funds_risk_less', 24, 8)->default(0); // 剩餘資金風險(金額)
            $table->float('target_rate', 24, 8)->default(0); // 開倉目標達成率
            $table->text('response'); // 購買後取得的資訊
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
        Schema::dropIfExists('admin_txn_buy_recs');
    }
}
