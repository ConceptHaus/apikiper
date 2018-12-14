<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDetalleMailings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalle_mailings', function (Blueprint $table) {
            $table->increments('id_detalle');
            $table->integer('id_mailing')->unsigned();
            $table->foreign('is_mailing')->references('id_mailing')->on('mailings')->onDelete('cascade');
            $table->string('subject');
            $table->string('preview_text');
            $table->string('text_body');
            $table->string('cta');
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
        Schema::dropIfExists('detalle_mailings');
    }
}
