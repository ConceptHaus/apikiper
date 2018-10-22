<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleProspecto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('detalle_prospecto', function (Blueprint $table) {
            $table->increments('id_detalle_prospecto')->unsigned();

            $table->uuid('id_prospecto');
            $table->foreign('id_prospecto')->references('id_prospecto')->on('prospectos')->onDelete('cascade');

            $table->string('puesto')->nullable();
            $table->string('empresa')->nullable();
            $table->string('telefono')->nullable();
            $table->string('celular')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('nota')->nullable();
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
          Schema::dropIfExists('detalle_prospecto');
    }
}
