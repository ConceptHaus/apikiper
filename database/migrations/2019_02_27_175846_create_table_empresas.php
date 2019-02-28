<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmpresas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->uuid('id_empresa');
            $table->primary('id_empresa');
            $table->string('nombre');
            $table->integer('cp')->nullable()->default(null);
            $table->string('calle')->nullable()->default(null);
            $table->string('colonia')->nullable()->default(null);
            $table->string('num_ext')->nullable()->default(null);
            $table->string('num_int')->nullable()->default(null);
            $table->string('pais')->nullable()->default(null);
            $table->string('estado')->nullable()->default(null);
            $table->string('municipio')->nullable()->default(null);
            $table->string('ciudad')->nullable()->default(null);
            $table->string('telefono')->nullable()->default(null);
            $table->integer('num_empleados')->nullable()->default(null);
            
            $table->integer('id_cat_industria')->unsigned()->nullable()->default(null);
            $table->foreign('id_cat_industria')->references('id_cat_industria')->on('cat_industrias')->onDelete('cascade');
            
            $table->string('web')->nullable()->default(null);
            $table->string('rfc')->nullable()->default(null);
            $table->string('razon_social')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('empresas');
    }
}
