<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAchievementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('question_no')->default(0);
            $table->unsignedInteger('answer_no')->default(0);
            $table->unsignedInteger('comment_no')->default(0);
            $table->unsignedInteger('points')->default(0);
            $table->unsignedInteger('achievement_no')->default(0);
            $table->char('nus_id', 8)->unique();
            $table->foreign('nus_id')->references('nus_id')->on('users');
        });

        // Schema::table('achievements', function(Blueprint $table){
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
        Schema::dropIfExists('achievements');
    }
}
