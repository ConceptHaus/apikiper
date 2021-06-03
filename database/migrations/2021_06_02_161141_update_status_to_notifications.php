<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateStatusToNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('status', ['escalado','resuelto'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
    //  */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
