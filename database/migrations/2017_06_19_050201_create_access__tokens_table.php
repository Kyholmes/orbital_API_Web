<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access__tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token', 500);
            $table->timestamps('date_created');
            $table->timestamps('date_expired');
            $table->string('nus_id', 8);
        });

        Schema::table('access__tokens', function(Blueprint $table){
            $table->primary('id');
            $table->foreign('nus_id')->references('nus_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('access__tokens');
    }
}
