<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTxnFeatSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_txn_feat_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');

            $table->float('initial_tradable_total_funds', 24, 8)->default(1); // 初始可交易總資金%
            $table->string('asset')->default('USDT'); // U本位幣種
            $table->float('initial_capital_risk', 24, 8)->default(0.1); // 每次交易資金風險%
            $table->float('liquidation_limit_price', 24, 8)->default(0.1); // 危及強平觸發限價
            $table->float('liquidation_prices', 24, 8)->default(0.1); // 危及強平價格

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
        Schema::dropIfExists('admin_txn_feat_settings');
    }
}
