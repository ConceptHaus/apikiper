<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDefaultProspectosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prospectos', function (Blueprint $table) {
            $table->string('nombre')->default('n/a')->change();
            $table->string('apellido')->default('n/a')->change();
            $table->string('correo')->default('n/a')->change();
            $table->integer('fuente')->default(3)->unsigned()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prospectos', function (Blueprint $table) {
            //
        });
    }
}
