<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

class AddPivotsToSignalHistoryUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('signal_history_user', function (Blueprint $table) {
            $table->json('asset')->default(new Expression('(JSON_ARRAY())'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('signal_history_user', function (Blueprint $table) {
            $table->dropColumn([
                'asset',
            ]);
        });
    }
}
