<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

class AddAssetToSignalHistoryUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('signal_history_user', function (Blueprint $table) {
            $table->text("asset")->nullable()->change();
            $table->renameColumn('asset', 'after_asset');
            $table->json('before_asset')->nullable();
        });

        Schema::table('signal_history_user', function (Blueprint $table) {
            $table->json("after_asset")->nullable()->change();
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
                'before_asset',
            ]);
            $table->text("after_asset")->nullable()->change();
            $table->renameColumn('after_asset', 'asset');
        });
        Schema::table('signal_history_user', function (Blueprint $table) {
            $table->json("asset")->nullable()->change();
        });
    }
}
