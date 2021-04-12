<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSetcol31ToFormulaTableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('formula_tables', function (Blueprint $table) {
            $table->string('setcol31')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('formula_tables', function (Blueprint $table) {
            $table->dropColumn([
                'setcol31'
            ]);
        });
    }
}
