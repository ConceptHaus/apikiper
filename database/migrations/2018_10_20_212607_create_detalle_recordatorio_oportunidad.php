<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleRecordatorioOportunidad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('detalle_recordatorio_op', function (Blueprint $table) {
            $table->increments('id_detalle_recordatorio')->unsigned();
            $table->integer('id_recordatorio_oportunidad')->unsigned();
            $table->foreign('id_recordatorio_oportunidad')->references('id_recordatorio_oportunidad')->on('recordatorios_oportunidad')->onDelete('cascade');
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
        Schema::dropIfExists('detalle_recordatorio_oportunidad');
    }
}
