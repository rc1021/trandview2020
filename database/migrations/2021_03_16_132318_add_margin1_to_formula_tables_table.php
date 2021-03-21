<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMargin1ToFormulaTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('formula_tables', function (Blueprint $table) {
            $table->string('setcol24')->nullable();
            $table->string('setcol25')->nullable();
            $table->string('setcol26')->nullable();
            $table->string('setcol27')->nullable();
            $table->string('setcol28')->nullable();
            $table->string('setcol29')->nullable();
            $table->string('setcol30')->nullable();
            $table->string('commit')->nullable();
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
                'setcol24',
                'setcol25',
                'setcol26',
                'setcol27',
                'setcol28',
                'setcol29',
                'setcol30',
                'commit',
            ]);
        });
    }
}
