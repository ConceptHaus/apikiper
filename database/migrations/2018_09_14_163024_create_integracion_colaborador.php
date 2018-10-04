<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegracionColaborador extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('integracion_colaborador', function (Blueprint $table) {
            $table->increments('id_integracion_colaborador')->unsigned();
            $table->uuid('id_colaborador');
            $table->foreign('id_colaborador')->references('id')->on('users')->onDelete('cascade');
            $table->integer('id_cat_integracion')->unsigned();
            $table->foreign('id_cat_integracion')->references('id_cat_integracion')->on('cat_integraciones')->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('integracion_colaborador');
    }
}
