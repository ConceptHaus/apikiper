<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArchivosOportunidadColaborador extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('archivos_oportunidad_colaborador', function (Blueprint $table) {
            $table->increments('id_archivos_oportunidad_colaborador')->unsigned();
            $table->uuid('id_colaborador');
            $table->foreign('id_colaborador')->references('id')->on('users')->onDelete('cascade');
            $table->uuid('id_oportunidad');
            $table->foreign('id_oportunidad')->references('id_oportunidad')->on('oportunidades')->onDelete('cascade');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->string('url');
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
        Schema::dropIfExists('archivos_oportunidad_colaborador');
    }
}
