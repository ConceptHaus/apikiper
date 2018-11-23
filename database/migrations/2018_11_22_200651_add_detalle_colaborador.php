<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDetalleColaborador extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detalle_colaborador', function (Blueprint $table) {

          $table->string('celular')->nullable();
          $table->string('whatsapp')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detalle_colaborador', function (Blueprint $table) {

          $table->string('celular')->nullable();
          $table->string('whatsapp')->nullable();

        });
    }
}
