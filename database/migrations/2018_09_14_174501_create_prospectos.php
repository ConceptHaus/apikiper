<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProspectos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('prospectos', function (Blueprint $table) {
            $table->uuid('id_prospecto');
            $table->primary('id_prospecto');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('correo');
            //$table->string('fuente');
            $table->integer('fuente')->unsigned();
            $table->foreign('fuente')->references('id_fuente')->on('cat_fuentes');
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
          Schema::dropIfExists('prospectos');
    }
}
