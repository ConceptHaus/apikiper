<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DetalleRecordatorio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('detalle_recordatorio', function (Blueprint $table) {
            $table->increments('id_detalle_recordatorio')->unsigned();
            $table->integer('id_recordatorio')->unsigned();
            $table->foreign('id_recordatorio')->references('id_recordatorio')->on('recordatorios')->onDelete('cascade');
            $table->string('fecha_recordatorio');
            $table->string('hora_recordatorio');
            $table->string('nota_recordatorio');
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
          Schema::dropIfExists('detalle_recordatorio');
    }
}
