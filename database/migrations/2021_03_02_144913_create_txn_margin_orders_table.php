<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTxnMarginOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('txn_margin_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');

            $table->string("symbol");
            $table->bigInteger("orderId");
            $table->string("clientOrderId")->nullable();
            $table->timestamp("transactTime", 0)->nullable();
            $table->string("price")->nullable();
            $table->string("origQty")->nullable();
            $table->string("executedQty")->nullable();
            $table->string("cummulativeQuoteQty")->nullable();
            $table->string("status")->nullable();
            $table->string("timeInForce")->nullable();
            $table->string("type")->nullable();
            $table->string("side")->nullable();
            $table->text("fills")->nullable();
            $table->string("marginBuyBorrowAsset")->nullable();
            $table->string("marginBuyBorrowAmount")->nullable();
            $table->boolean("isIsolated")->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('txn_margin_orders');
    }
}
