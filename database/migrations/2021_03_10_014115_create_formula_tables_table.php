<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormulaTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formula_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');

            $table->string('setcol1')->nullable();
            $table->string('setcol2')->nullable();
            $table->string('setcol3')->nullable();
            $table->string('setcol4')->nullable();
            $table->string('setcol5')->nullable();
            $table->string('setcol6')->nullable();
            $table->string('setcol7')->nullable();
            $table->string('setcol8')->nullable();
            $table->string('setcol9')->nullable();
            $table->string('setcol10')->nullable();
            $table->string('setcol11')->nullable();
            $table->string('setcol12')->nullable();
            $table->string('setcol13')->nullable();
            $table->string('setcol14')->nullable();
            $table->string('setcol15')->nullable();
            $table->string('setcol16')->nullable();
            $table->string('setcol17')->nullable();
            $table->string('setcol18')->nullable();
            $table->string('setcol19')->nullable();
            $table->string('setcol20')->nullable();
            $table->string('setcol21')->nullable();
            $table->string('setcol22')->nullable();
            $table->string('setcol23')->nullable();
            $table->string('file_path')->nullable();
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
        Schema::dropIfExists('formula_tables');
    }
}
