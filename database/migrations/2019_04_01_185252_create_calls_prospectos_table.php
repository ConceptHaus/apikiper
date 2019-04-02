<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCallsProspectosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calls_prospectos', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('id_prospecto');
            $table->foreign('id_prospecto')->references('id_prospecto')->on('prospectos')->onDelete('cascade');
            $table->string('caller_number')->nullable();
            $table->string('caller_name')->nullable();
            $table->string('caller_city')->nullable();
            $table->string('caller_state')->nullable();
            $table->string('caller_zip')->nullable();
            $table->string('play_recording')->nullable();
            $table->string('device_type')->nullable();
            $table->string('device_make')->nullable();
            $table->string('call_status')->nullable();
            $table->string('call_duration')->nullable();


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
        Schema::dropIfExists('calls_prospectos');
    }
}
