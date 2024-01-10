<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletableAndRoleToCatStatusOportunidad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cat_status_oportunidad', function (Blueprint $table) {
            $table->integer('deletable')->after('funnel_order')->default(1);
            $table->char('role', 25)->after('deletable')->nullable();
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
            $table->dropColumn('deletable');
            $table->dropColumn('role');
        });
    }
}
