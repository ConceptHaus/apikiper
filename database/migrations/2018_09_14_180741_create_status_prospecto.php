<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusProspecto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('status_prospecto', function (Blueprint $table) {
            $table->increments('id_status_prospecto')->unsigned();

            $table->integer('id_cat_status_prospecto')->unsigned();
            $table->foreign('id_cat_status_prospecto')->references('id_cat_status_prospecto')->on('cat_status_prospecto')->onDelete('cascade');

            $table->uuid('id_prospecto');
            $table->foreign('id_prospecto')->references('id_prospecto')->on('prospectos')->onDelete('cascade');


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
        Schema::dropIfExists('status_prospecto');
    }
}
