<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TxnSettingType;
use Illuminate\Database\Query\Expression;

class AddPairToAdminTxnSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_txn_settings', function (Blueprint $table) {
            $table->integer('type')->default(TxnSettingType::Margin);
            $table->string('pair');
            $table->json('options')->default(new Expression('(JSON_ARRAY())'));
            $table->unique(['user_id', 'pair'], 'pair');

            $table->dropColumn([
                'initial_tradable_total_funds',
                'initial_capital_risk',
                'lever_switch',
                'btc_daily_interest',
                'usdt_daily_interest',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_txn_settings', function (Blueprint $table) {

            $table->float('initial_tradable_total_funds', 24, 8)->default(1);
            $table->float('initial_capital_risk', 24, 8)->default(0.1);
            $table->tinyInteger('lever_switch')->default(1);
            $table->float('btc_daily_interest', 24, 8)->default(0.0003);
            $table->float('usdt_daily_interest', 24, 8)->default(0.0015);
            $table->dropUnique('pair');

            $table->dropColumn([
                'type',
                'pair',
                'options',
            ]);
        });
    }
}
