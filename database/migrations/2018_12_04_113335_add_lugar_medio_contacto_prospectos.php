<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLugarMedioContactoProspectos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('medio_contacto_prospectos', function (Blueprint $table) {
            $table->string('lugar')->nullable();
            $table->dateTime('fecha')->nullable()->change();
            $table->string('hora')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('medio_contacto_prospectos', function (Blueprint $table) {
            $table->dropColumn('lugar')->nullable();
        });
    }
}
