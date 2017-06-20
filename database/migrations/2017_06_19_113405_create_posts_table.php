<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('img_link', 500)->nullable();
            $table->string('title', 300);
            $table->text('question_descrip')->nullable();
            $table->unsignedInteger('vote')->default(0);
            $table->unsignedInteger('subscribe_no')->default(0);
            $table->timestamp('created_date');
            $table->timestamp('updated_date');
            $table->timestamp('expired_date')->nullable();
            $table->boolean('time_limit')->default(false);
            $table->unsignedInteger('points')->nullable();
            $table->unsignedInteger('nus_id');
            $table->foreign('nus_id')->references('nus_id')->on('users');
        });

        // Schema::table('posts', function(Blueprint $table){
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
        Schema::dropIfExists('posts');
    }
}
