<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_infos', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            
            $table->uuid('id_prospecto');
            $table->foreign('id_prospecto')->references('id_prospecto')->on('prospectos')->onDelete('cascade');
            
            $table->integer('id_forms')->unsigned();
            $table->foreign('id_forms')->references('id_integracion_forms')->on('integracion_forms')->onDelete('cascade');

            $table->string('utm_term')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('ad_position')->nullable();
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
        Schema::dropIfExists('campaign_infos');
    }
}
