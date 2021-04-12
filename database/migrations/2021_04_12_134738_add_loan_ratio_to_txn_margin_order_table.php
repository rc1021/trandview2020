<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoanRatioToTxnMarginOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('txn_margin_orders', function (Blueprint $table) {
            $table->float('loan_ratio', 3, 1)->default(0);
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
                'loan_ratio'
            ]);
        });
    }
}
