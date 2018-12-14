<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableImagesMailings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images_mailings', function (Blueprint $table) {
            $table->increments('id_image');
            $table->integer('id_mailing')->unsigned();
            $table->foreign('id_mailing')->references('id_mailing')->on('mailings')->onDelete('cascade');
            $table->bolean('is_logo');
            $table->string('url');
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
        Schema::dropIfExists('images_mailings');
    }
}
