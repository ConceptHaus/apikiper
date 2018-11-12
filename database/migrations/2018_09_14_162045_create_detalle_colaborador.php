<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleColaborador extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('detalle_colaborador', function (Blueprint $table) {
            $table->increments('id_detalle_colaborador')->unsigned();
            $table->uuid('id_colaborador');
            $table->foreign('id_colaborador')->references('id')->on('users')->onDelete('cascade');
            $table->string('puesto')->nullable();
            $table->string('telefono')->nullable();
            $table->string('fecha_nacimiento')->nullable();
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
          Schema::dropIfExists('detalle_colaborador');
    }
}
