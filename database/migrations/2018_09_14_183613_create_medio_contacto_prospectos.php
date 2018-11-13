<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedioContactoProspectos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('medio_contacto_prospectos', function (Blueprint $table) {
            $table->increments('id_medio_contacto_prospecto')->unsigned();
            $table->integer('id_mediocontacto_catalogo')->unsigned();
            $table->foreign('id_mediocontacto_catalogo')->references('id_mediocontacto_catalogo')->on('mediocontacto_catalogo')->onDelete('cascade');
            $table->uuid('id_prospecto');
            $table->foreign('id_prospecto')->references('id_prospecto')->on('prospectos')->onDelete('cascade');
            $table->string('descripcion')->nullable();
            $table->dateTime('fecha');
            $table->string('hora');
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
            Schema::dropIfExists('medio_contacto_prospectos');
    }
}
