<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TxnSettingType;

class AddTypeToSignalHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('signal_histories', function (Blueprint $table) {
            $table->integer('type')->default(TxnSettingType::Margin);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('signal_histories', function (Blueprint $table) {
            $table->dropColumn([
                'type'
            ]);
        });
    }
}
