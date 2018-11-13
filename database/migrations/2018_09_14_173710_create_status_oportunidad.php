<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusOportunidad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('status_oportunidad', function (Blueprint $table) {
            $table->increments('id_status_oportunidad')->unsigned();
            $table->uuid('id_oportunidad');
            $table->foreign('id_oportunidad')->references('id_oportunidad')->on('oportunidades')->onDelete('cascade');
            $table->integer('id_cat_status_oportunidad')->unsigned();
            $table->foreign('id_cat_status_oportunidad')->references('id_cat_status_oportunidad')->on('cat_status_oportunidad')->onDelete('cascade');
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
        Schema::dropIfExists('status_oportunidad');
    }
}
