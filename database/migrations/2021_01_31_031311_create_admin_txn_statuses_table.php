<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTxnStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_txn_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');

            $table->tinyInteger('tranding_switch')->default(0); // 交易開關
            $table->tinyInteger('current_state')->default(0); // 當前狀態 0平倉; 1持倉
            $table->float('initial_total_capital', 24, 8)->default(0); // 初始總資金
            $table->tinyInteger('trading_program_status')->default(0); // 交易程式狀態 0停止; 1運作中
            $table->integer('total_transaction_times')->default(0); // 總交易次數
            $table->integer('total_number_of_short_times')->default(0); // 總做空次數
            $table->integer('total_number_of_long_times')->default(0); // 總做多次數
            $table->integer('use_leverage')->default(0); // 使用槓桿率(%)
            $table->integer('average_capital_risk_during_the_period')->default(0); // 期間平均資金風險(%)
            $table->integer('total_profit_times')->default(0); // 總獲利次數
            $table->integer('total_loss_times')->default(0); // 總損失次數
            $table->integer('total_profit')->default(0); // 總獲利
            $table->integer('total_loss')->default(0); // 總損失
            $table->integer('profit_and_loss')->default(0); // 總損益
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
        Schema::dropIfExists('admin_txn_statuses');
    }
}
