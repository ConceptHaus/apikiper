<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEtiquetasOportunidades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('etiquetas_oportunidades', function (Blueprint $table) {
            $table->increments('id_etiqueta_oportunidad')->unsigned();

            $table->integer('id_etiqueta')->unsigned();
            $table->foreign('id_etiqueta')->references('id_etiqueta')->on('etiquetas')->onDelete('cascade');

            $table->uuid('id_oportunidad');
            $table->foreign('id_oportunidad')->references('id_oportunidad')->on('oportunidades')->onDelete('cascade');

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
        //
          Schema::dropIfExists('etiquetas_oportunidades');
    }
}
