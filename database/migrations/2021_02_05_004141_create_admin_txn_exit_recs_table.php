<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTxnExitRecsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_txn_exit_recs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('signal_history_id');

            $table->dateTime('H29'); // 應平倉日期時間
            $table->float('H30', 24, 8)->default(0); // Exit訊號價位(當時的價位)
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
        Schema::dropIfExists('admin_txn_exit_recs');
    }
}
