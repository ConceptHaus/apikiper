<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProspectosEmpresas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('prospectos_empresas', function (Blueprint $table) {
            $table->increments('id_prospecto_empresa')->unsigned();

            $table->uuid('id_prospecto')->unique();
            $table->foreign('id_prospecto')->references('id_prospecto')->on('prospectos')->onDelete('cascade');

            $table->uuid('id_empresa');
            $table->foreign('id_empresa')->references('id_empresa')->on('empresas')->onDelete('cascade');
            
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
          Schema::dropIfExists('prospectos_empresas');
    }
}
