<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicioProspecto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('servicio_prospecto', function (Blueprint $table) {
            $table->increments('id_servicio_prospecto')->unsigned();

            $table->uuid('id_prospecto');
            $table->foreign('id_prospecto')->references('id_prospecto')->on('prospectos')->onDelete('cascade');


            $table->integer('id_servicio_cat')->unsigned();
            $table->foreign('id_servicio_cat')->references('id_servicio_cat')->on('cat_servicios')->onDelete('cascade');


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
        Schema::dropIfExists('servicio_prospecto');
    }
}
