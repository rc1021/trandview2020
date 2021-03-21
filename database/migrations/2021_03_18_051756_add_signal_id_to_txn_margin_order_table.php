<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSignalIdToTxnMarginOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('txn_margin_orders', function (Blueprint $table) {
            $table->foreignId('signal_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('txn_margin_orders', function (Blueprint $table) {
            $table->dropColumn([
                'signal_id',
            ]);
        });
    }
}
