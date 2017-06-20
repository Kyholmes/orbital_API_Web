<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('tag', 100);
            $table->string('description', 250)->nullable();
            $table->timestamp("last_update");
            $table->unsignedInteger('subscribe_no');
            $table->char('created_by', 8);
            $table->foreign('created_by')->references('nus_id')->on('users');
        });

        // Schema::table('tags', function(Blueprint $table){
        //     $table->primary('id');
        //     $table->foreign('created_by')->references('nus_id')->on('users');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags');
    }
}
