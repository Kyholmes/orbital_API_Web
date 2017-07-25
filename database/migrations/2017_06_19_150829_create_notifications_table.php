<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamp('created_date');
            $table->timestamp('expired_date');
            $table->tinyInteger('read')->default(0);
            $table->char('nus_id', 8);
            $table->unsignedInteger('notification_type');
            $table->unsignedInteger('comment_id')->nullable()->default(NULL);
            $table->unsignedInteger('post_id')->nullable()->default(NULL);
            $table->unsignedInteger('tag_id')->nullable()->default(NULL);
            $table->foreign('post_id')->references('id')->on('posts');
            $table->foreign('nus_id')->references('nus_id')->on('users');
            $table->foreign('comment_id')->references('id')->on('comments');
            $table->foreign('notification_type')->references('id')->on('notification__types');
            $table->foreign('tag_id')->references('id')->on('tags');
        });

        // Schema::table('notifications', function(Blueprint $table){
        //     $table->primary('id');
        //     $table->foreign('post_id')->references('id')->on('posts');
        //     $table->foreign('nus_id')->references('nus_id')->on('users');
        //     $table->foreign('comment_id')->references('comment_id')->on('comments');
        //     $table->foreign('notification_type')->references('id')->on('notification__types');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
