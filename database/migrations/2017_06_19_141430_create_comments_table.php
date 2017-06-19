<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->text('description');
            $table->boolean('best_answer')->default('f');
            $table->char('reply_to_nus_id', 8)->nullable();
            $table->char('nus_id', 8);
            $table->unsignedInteger('post_id');
            $table->string('img_link', 500)->nullable();
            $table->timestamps('created_date');
            $table->unsignedInteger('comment_id')->nullable();
            $table->timestamps('updated_date');
            $table->unsignedInteger('vote')->default(0);
        });

        Schema::table('comments', function(Blueprint $table){
            $table->foreign('post_id')->references('id')->on('posts');
            $table->foreign('nus_id')->references('nus_id')->on('users');
            $table->foreign('comment_id')->references('comment_id')->on('comments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
