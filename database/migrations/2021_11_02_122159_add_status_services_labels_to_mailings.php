<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusServicesLabelsToMailings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mailings', function (Blueprint $table) {
            $table->integer('status')->nullable();
            $table->integer('service')->nullable();
            $table->integer('label')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mailings', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('service');
            $table->dropColumn('label');
        });
    }
}
