<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToRecordatoriosProspecto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recordatorios_oportunidad', function (Blueprint $table) {
            $table->integer('status')->after('id_colaborador')->default(0)->comment("0=No Enviado, 1=Enviado");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recordatorios_oportunidad', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
