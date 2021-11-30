<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlagSimpleEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detalle_mailings', function (Blueprint $table) {
            $table->integer('simple_email')->default(0)->comment("0=Custom, 1=Simple");
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
            $table->dropColumn('simple_email');
        });
    }
}
