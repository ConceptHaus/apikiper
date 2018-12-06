<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableIntegracionForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integracion_forms', function (Blueprint $table) {
            $table->increments('id_integracion_forms');
            $table->string('token')->unique();
            $table->string('url_success');
            $table->string('url_error');
            $table->string('nombre');
            $table->integer('total')->default(0);
            $table->boolean('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integracion_forms');
    }
}
