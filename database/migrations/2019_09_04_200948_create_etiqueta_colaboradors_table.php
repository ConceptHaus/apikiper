<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEtiquetaColaboradorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('etiqueta_colaboradors', function (Blueprint $table) {
            $table->increments('id_et_col')->unsigned();

            $table->uuid('id_user');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');

            $table->integer('id_etiqueta')->unsigned();
            $table->foreign('id_etiqueta')->references('id_etiqueta')->on('etiquetas')->onDelete('cascade');
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
        Schema::dropIfExists('etiqueta_colaboradors');
    }
}
