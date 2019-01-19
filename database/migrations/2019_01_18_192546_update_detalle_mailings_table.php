<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDetalleMailingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detalle_mailings', function (Blueprint $table){
            
            //$table->integer('opcion_servicio')->unsigned()->default(1);
            $table->foreign('opcion_servicio')->references('id_servicio_cat')->on('cat_servicios')->onDelete('cascade');
            //ligado con la tabla de cat_servicios
            //$table->integer('opcion_etiqueta')->unsigned()->default(1);
            $table->foreign('opcion_etiqueta')->references('id_etiqueta')->on('etiquetas')->onDelete('cascade');
            //ligado con la tabla de etiquetas
            //$table->integer('opcion_status')->unsigned()->default(1);
            $table->foreign('opcion_status')->references('id_cat_status_oportunidad')->on('cat_status_oportunidad')->onDelete('cascade');
            //ligado con la tabla de cat_status_oportunidad
            //$table->string('fondo_general');
            //$table->string('fondo_cta');
            //$table->string('color_titulo');
            //$table->string('color_subtitulo');
            //$table->string('color_lineas');
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
    }
}
