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

            $table->dateTime('F29');	// 	開倉交易起始日期時間
            $table->dateTime('F30');	// 	開倉交易完成日期時間
            $table->integer('F31')->default(0);	// 	開倉交易持續時間
            $table->float('F32', 24, 8)->default(0);	// 	實際開倉部位大小(美元)
            $table->float('F33', 24, 8)->default(0);	// 	實際開倉價位(均價)
            $table->float('F34', 24, 8)->default(0);	// 	實際開倉(幾口)
            $table->float('F35', 24, 8)->default(0);	// 	實際借款金額
            $table->float('F36', 24, 8)->default(0);	// 	實際起始風險%(1R)
            $table->float('F37', 24, 8)->default(0);	// 	交易手續費
            $table->float('F38', 24, 8)->default(0);	// 	實際資金風險(金額)
            $table->float('F39', 24, 8)->default(0);	// 	剩餘資金風險(金額)
            $table->float('F40', 24, 8)->default(0);	// 	開倉目標達成率
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
