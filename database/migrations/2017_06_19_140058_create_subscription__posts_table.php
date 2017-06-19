<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription__posts', function (Blueprint $table) {
            $table->unsignedInteger('post_id');
            $table->unsignedInteger('nus_id');
            $table->timestamps('last_visit');
        });

        Schema::table('posts', function(Blueprint $table){
            $table->foreign('post_id')->references('id')->on('posts');
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
        Schema::dropIfExists('subscription__posts');
    }
}
