<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription__tags', function (Blueprint $table) {
            $table->char('nus_id');
            $table->unsignedInteger('tag_id');
            $table->timestamps('last_visit');
        });

        Schema::table('subscription__tags', function(Blueprint $table){
            $table->foreign('nus_id')->references('nus_id')->on('users');
            $table->foreign('tag_id')->references('id')->on('tags');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription__tags');
    }
}
