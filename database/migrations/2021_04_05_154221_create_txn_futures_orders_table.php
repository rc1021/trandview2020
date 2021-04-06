<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTxnFuturesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('txn_futures_orders', function (Blueprint $table) {
            $table->id();

            $table->string("clientOrderId"); //"testOrder", // 用户自定义的订单号
            $table->string("cumQty"); //"0",
            $table->string("cumQuote"); //"0", // 成交金额
            $table->string("executedQty"); //"0", // 成交量
            $table->string("orderId"); //22542179, // 系统订单号
            $table->string("avgPrice"); //"0.00000",  // 平均成交价
            $table->string("origQty"); //"10", // 原始委托数量
            $table->string("price"); //"0", // 委托价格
            $table->boolean("reduceOnly"); //false, // 仅减仓
            $table->string("side"); //"SELL", // 买卖方向
            $table->string("positionSide"); //"SHORT", // 持仓方向
            $table->string("status"); //"NEW", // 订单状态
            $table->string("stopPrice"); //"0", // 触发价，对`TRAILING_STOP_MARKET`无效
            $table->boolean("closePosition"); //false,   // 是否条件全平仓
            $table->string("symbol"); //"BTCUSDT", // 交易对
            $table->string("timeInForce"); //"GTC", // 有效方法
            $table->string("type"); //"TRAILING_STOP_MARKET", // 订单类型
            $table->string("origType"); //"TRAILING_STOP_MARKET",  // 触发前订单类型
            $table->string("activatePrice"); //"9020", // 跟踪止损激活价格, 仅`TRAILING_STOP_MARKET` 订单返回此字段
            $table->string("priceRate"); //"0.3", // 跟踪止损回调比例, 仅`TRAILING_STOP_MARKET` 订单返回此字段
            $table->string("updateTime"); //1566818724722, // 更新时间
            $table->string("workingType"); //"CONTRACT_PRICE", // 条件价格触发类型
            $table->boolean("priceProtect"); //false            // 是否开启条件单触发保护

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
        Schema::dropIfExists('txn_futures_orders');
    }
}
