<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleEventoOportunidad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::create('detalle_evento_oportunidad', function (Blueprint $table) {

            $table->increments('id_detalle_evento_oportunidad')->unsigned();

            $table->integer('id_evento_oportunidad')->unsigned();
            $table->foreign('id_evento_oportunidad')->references('id_evento_oportunidad')->on('eventos_oportunidad')->onDelete('cascade');

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
         Schema::dropIfExists('detalle_evento_oportunidad');
    }
}
