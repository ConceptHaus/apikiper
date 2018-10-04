<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateColaboradorProspecto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('colaborador_prospecto', function (Blueprint $table) {
            $table->increments('id_colaborador_prospecto')->unsigned();

            $table->uuid('id_colaborador');
            $table->foreign('id_colaborador')->references('id')->on('users')->onDelete('cascade');

            $table->uuid('id_prospecto');
            $table->foreign('id_prospecto')->references('id_prospecto')->on('prospectos')->onDelete('cascade');

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
          Schema::dropIfExists('colaborador_prospecto');
    }
}
