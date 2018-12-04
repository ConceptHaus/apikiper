<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedioContactoOportunidades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medio_contacto_oportunidades', function (Blueprint $table) {
            $table->increments('id_medio_contacto_oportunidad')->unsigned();
            $table->integer('id_mediocontacto_catalogo')->unsigned();
            $table->foreign('id_mediocontacto_catalogo')->references('id_mediocontacto_catalogo')->on('mediocontacto_catalogo')->onDelete('cascade');
            $table->uuid('id_oportunidad');
            $table->foreign('id_oportunidad')->references('id_oportunidad')->on('oportunidades')->onDelete('cascade');
            $table->string('descripcion')->nullable();
            $table->dateTime('fecha')->nullable();
            $table->string('hora')->nullable();
            $table->string('lugar')->nullable();
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
        Schema::dropIfExists('medio_contacto_oportunidads');
    }
}
