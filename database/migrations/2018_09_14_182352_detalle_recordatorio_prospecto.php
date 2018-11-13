<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DetalleRecordatorioProspecto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('detalle_recordatorio_prospecto', function (Blueprint $table) {
            $table->increments('id_detalle_recordatorio')->unsigned();
            $table->integer('id_recordatorio_prospecto')->unsigned();
            $table->foreign('id_recordatorio_prospecto')->references('id_recordatorio_prospecto')->on('recordatorios_prospecto')->onDelete('cascade');
            $table->dateTime('fecha_recordatorio');
            $table->string('hora_recordatorio');
            $table->string('nota_recordatorio')->nullable();
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
          Schema::dropIfExists('detalle_recordatorio');
    }
}
