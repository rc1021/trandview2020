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

            $table->dateTime('J29'); // 平倉交易起始日期時間
            $table->dateTime('J30'); // 平倉交易結束日期時間
            $table->integer('J31')->default(0); // 平倉交易持續時間
            $table->float('J32', 24, 8)->default(0); // 平倉價位(均價)
            $table->float('J33', 24, 8)->default(0); // 交易手續費
            $table->float('J34', 24, 8)->default(0); // 取回資金
            $table->float('J35', 24, 8)->default(0); // 損益
            $table->float('J36', 24, 8)->default(0); // 損益率(%)
            $table->float('J37', 24, 8)->default(0); // R值
            $table->float('J38', 24, 8)->default(0); // 交易後可交易總資金
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
