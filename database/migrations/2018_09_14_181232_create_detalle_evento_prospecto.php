<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleEventoProspecto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('detalle_evento_prospecto', function (Blueprint $table) {
            $table->increments('id_detalle_evento_prospecto')->unsigned();

            $table->integer('id_evento_prospecto')->unsigned();
            $table->foreign('id_evento_prospecto')->references('id_evento_prospecto')->on('eventos_prospecto')->onDelete('cascade');

            $table->string('lugar_evento')->nullable();
            $table->dateTime('fecha_evento');
            $table->string('hora_evento');
            $table->string('nota_evento')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('detalle_evento_prospecto');
    }
}
