<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColorDetalleMailingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detalle_mailings', function (Blueprint $table) {
            $table->string('color_fuente')->nullable()->default('#F4F4F4');
            $table->string('color_cta')->nullable()->default('#F4F4F4');
            $table->string('subtitle')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detalle_mailings', function (Blueprint $table) {
            //
        });
    }
}
