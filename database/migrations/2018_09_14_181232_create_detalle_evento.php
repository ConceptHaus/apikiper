<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleEvento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('detalle_evento', function (Blueprint $table) {
            $table->increments('id_detalle_evento')->unsigned();

            $table->integer('id_evento')->unsigned();
            $table->foreign('id_evento')->references('id_evento')->on('eventos')->onDelete('cascade');

            $table->string('lugar_evento');
            $table->string('fecha_evento');
            $table->string('hora_evento');
            $table->string('nota_evento');

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
        Schema::dropIfExists('detalle_evento');
    }
}
