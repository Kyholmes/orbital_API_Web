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
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('token', 500);
            $table->timestamp('created_date');
            $table->timestamp('expired_date');
            $table->string('nus_id', 8);
            $table->foreign('nus_id')->references('nus_id')->on('users');
        });

        // Schema::table('access__tokens', function(Blueprint $table){
        //     $table->primary('id');
        //     $table->foreign('nus_id')->references('nus_id')->on('users');
        // });
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
