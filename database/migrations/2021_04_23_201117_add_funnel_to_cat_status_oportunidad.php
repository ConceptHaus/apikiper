<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFunnelToCatStatusOportunidad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cat_status_oportunidad', function (Blueprint $table) {
            $table->integer('funnel_visible')->after('descripcion')->default(0);
            $table->integer('funnel_order')->after('funnel_visible')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cat_status_oportunidad', function (Blueprint $table) {
            $table->dropColumn('funnel_visible');
            $table->dropColumn('funnel_order');
        });
    }
}
