<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToRecordatoriosUsuarios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recordatorio_colaborador', function (Blueprint $table) {
            $table->integer('status')->after('nota')->default(0)->comment("0=No Enviado, 1=Enviado");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recordatorio_colaborador', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
