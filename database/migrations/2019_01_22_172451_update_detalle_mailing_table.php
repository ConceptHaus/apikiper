<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDetalleMailingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detalle_mailings', function (Blueprint $table) {
            $table->string('preview_text')->nullable()->default(null)->change();
            $table->string('text_body')->nullable()->default(null)->change();
            $table->string('cta_nombre')->nullable()->default(null)->change();
            $table->string('cta_url')->nullable()->default(null)->change();
            $table->string('color')->nullable()->default(null)->change();
            $table->string('color_fuente')->nullable()->default(null)->change();
            $table->string('color_cta')->nullable()->default(null)->change();
            $table->string('fondo_general')->nullable()->default(null);
            $table->string('fondo_cta')->nullable()->default(null);
            $table->string('color_titulo')->nullable()->default(null);
            $table->string('color_subtitulo')->nullable()->default(null);
            $table->string('color_lineas')->nullable()->default(null);


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
    }
}
