<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFontAndSizeForMailing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detalle_mailings', function (Blueprint $table) {
            $table->string('fuente_titulo')->nullable();
            $table->string('fuente_size_titulo')->nullable();
            $table->string('fuente_subtitulo')->nullable();
            $table->string('fuente_size_subtitulo')->nullable();
            $table->string('fuente_descripcion')->nullable();
            $table->string('fuente_size_descripcion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detalle_mailings', function (Blueprint $table) {
            $table->dropColumn('fuente_titulo');
            $table->dropColumn('fuente_size_titulo');
            $table->dropColumn('fuente_subtitulo');
            $table->dropColumn('fuente_size_subtitulo');
            $table->dropColumn('fuente_descripcion');
            $table->dropColumn('fuente_size_descripcion');
        });
    }
}
