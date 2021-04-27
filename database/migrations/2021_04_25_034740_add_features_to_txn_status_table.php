<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeaturesToTxnStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_txn_statuses', function (Blueprint $table) {
            $table->boolean("current_feat_state");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_txn_statuses', function (Blueprint $table) {
            $table->dropColumn([
                'current_feat_state',
            ]);
        });
    }
}
