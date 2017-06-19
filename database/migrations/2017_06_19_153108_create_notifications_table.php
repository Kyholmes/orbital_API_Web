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
            $table->increments('id');
            $table->timestamps('created_date')
            $table->timestamps('expired_date');
            $table->boolean('read')->default('f');
            $table->char('nus_id', 8);
            $table->unsignedInteger('notification_type');
            $table->unsignedInteger('comment_id')->nullable();
            $table->unsignedInteger('post_id')->nullable();
        });

        Schema::table('notifications', function(Blueprint $table){
            $table->primary('id');
            $table->foreign('post_id')->references('id')->on('posts');
            $table->foreign('nus_id')->references('nus_id')->on('users');
            $table->foreign('comment_id')->references('comment_id')->on('comments');
            $table->foreign('notification_type')->references('id')->on('notification__types');
        });
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
