<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateNombreprospectoToProspectos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        return DB::select("ALTER TABLE prospectos MODIFY nombre varchar(255) null;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        return DB::select("ALTER TABLE prospectos MODIFY nombre varchar(255) not null;");
    }
}


