<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use BinanceApi\Enums\SymbolType;

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

            $table->string('symbol')->default(SymbolType::BTCUSDT);
            $table->bigInteger('orderId');
            $table->string('type');
            $table->text('result');
            $table->string('error')->nullable();
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
