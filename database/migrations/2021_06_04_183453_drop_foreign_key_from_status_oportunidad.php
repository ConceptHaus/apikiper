<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropForeignKeyFromStatusOportunidad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        Schema::table('status_oportunidad', function (Blueprint $table) {
            $table->dropForeign('status_oportunidad_id_cat_status_oportunidad_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('status_oportunidad', function (Blueprint $table) {
            $table->foreign('id_cat_status_oportunidad')->references('id_cat_status_oportunidad')->on('cat_status_oportunidad')->onDelete('cascade');
        });
        
    }
}
