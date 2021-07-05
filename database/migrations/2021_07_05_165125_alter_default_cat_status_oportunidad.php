<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDefaultCatStatusoportunidad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::select("UPDATE cat_status_oportunidad SET funnel_visible = 1 WHERE cat_status_oportunidad.id_cat_status_oportunidad = 1;");
        DB::select("UPDATE cat_status_oportunidad SET funnel_visible = 1 WHERE cat_status_oportunidad.id_cat_status_oportunidad = 2;");
        DB::select("UPDATE cat_status_oportunidad SET funnel_order = 1 WHERE cat_status_oportunidad.id_cat_status_oportunidad = 1;");
        DB::select("UPDATE cat_status_oportunidad SET funnel_order = 8 WHERE cat_status_oportunidad.id_cat_status_oportunidad = 2;");
        DB::select("UPDATE cat_status_oportunidad SET role = 'oportunidad-success' WHERE cat_status_oportunidad.id_cat_status_oportunidad = 2;");
        DB::select("UPDATE cat_status_oportunidad SET role = 'oportunidad-failure' WHERE cat_status_oportunidad.id_cat_status_oportunidad = 3;");
        DB::select("UPDATE cat_status_oportunidad SET deletable = 0 WHERE cat_status_oportunidad.id_cat_status_oportunidad = 1;");
        DB::select("UPDATE cat_status_oportunidad SET deletable = 0 WHERE cat_status_oportunidad.id_cat_status_oportunidad = 2;");
        DB::select("UPDATE cat_status_oportunidad SET deletable = 0 WHERE cat_status_oportunidad.id_cat_status_oportunidad = 3;");
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}


