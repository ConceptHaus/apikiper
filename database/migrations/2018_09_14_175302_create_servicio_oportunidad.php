<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicioOportunidad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('servicio_oportunidad', function (Blueprint $table) {
            $table->increments('id_servicio_oportunidad')->unsigned();

            $table->uuid('id_oportunidad');
            $table->foreign('id_oportunidad')->references('id_oportunidad')->on('oportunidades')->onDelete('cascade');

            $table->integer('id_servicio_cat')->unsigned();
            $table->foreign('id_servicio_cat')->references('id_servicio_cat')->on('cat_servicios')->onDelete('cascade');

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
            Schema::dropIfExists('servicio_oportunidad');
    }
}
