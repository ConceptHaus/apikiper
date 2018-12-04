<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemindersToRecordatoriosProspecto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recordatorios_prospecto', function (Blueprint $table) {
            $table->boolean('notification_sent')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recordatorios_prospecto', function (Blueprint $table) {
            $table->boolean('notification_sent')->default(0);
        });
    }
}
