<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOportunidadProspectoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oportunidad_prospecto', function (Blueprint $table) {
            $table->increments('id_oportunidad_prospecto');
            $table->uuid('id_prospecto');
            $table->foreign('id_prospecto')->references('id_prospecto')->on('prospectos')->onDelete('cascade');
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
        Schema::dropIfExists('opotunidad_prospecto');
    }
}
