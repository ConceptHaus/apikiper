<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailingInboxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailing_inbox', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id');
            $table->string('password');
            $table->string('host');
            $table->string('port');
            $table->string('encryption')->default('ssl');
            $table->string('alt_email')->nullable();
            $table->timestamps();
            //Relations
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mailing_inbox');
    }
}
