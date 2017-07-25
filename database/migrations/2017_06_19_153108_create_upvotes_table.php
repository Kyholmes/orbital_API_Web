<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpvotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upvotes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->char('nus_id', 8);
            $table->unsignedInteger('post_id')->nullable()->default(NULL);
            $table->unsignedInteger('comment_id')->nullable()->default(NULL);
            $table->foreign('nus_id')->references('nus_id')->on('users');
            $table->foreign('post_id')->references('id')->on('posts');
            $table->foreign('comment_id')->references('id')->on('comments');
        });

        // Schema::table('upvotes', function(Blueprint $table){
        //     $table->primary('id');
        //     $table->foreign('nus_id')->references('nus_id')->on('users');
        //     $table->foreign('post_id')->references('id')->on('posts');
        //     $table->foreign('post_id')->references('id')->on('replys');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upvotes');
    }
}
