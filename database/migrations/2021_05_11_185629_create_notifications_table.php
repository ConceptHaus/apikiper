<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('colaborador_id');
            $table->uuid('source_id');
            $table->enum('notification_type', ['prospecto', 'oportunidad']);
            $table->integer('inactivity_period')->comment('amount|hours');
            $table->enum('status', ['no-leido', 'leido'])->default('no-leido');
            $table->integer('attempts')->default(0);
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
        Schema::dropIfExists('notifications');
    }
}
