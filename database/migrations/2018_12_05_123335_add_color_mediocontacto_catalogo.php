<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColorMediocontactoCatalogo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mediocontacto_catalogo', function (Blueprint $table) {
            $table->string('color')->nullable()->default('#F4F4F4');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mediocontacto_catalogo', function (Blueprint $table) {
            $table->string('color')->nullable()->default('#F4F4F4');
        });
    }
}
