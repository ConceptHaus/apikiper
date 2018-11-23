<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateProspectosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prospectos', function (Blueprint $table) {
            $table->integer('fuente')->unsigned()->change();
            $table->foreign('fuente')->references('id_fuente')->on('cat_fuentes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prospectos', function (Blueprint $table) {
            //
        });
    }
}
