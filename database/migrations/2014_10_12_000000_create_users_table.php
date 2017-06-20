<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->char('nus_id', 8)->unique();
            $table->string('name', 50);
            $table->string('username', 50)->unique();
            $table->string('password',100);
            $table->string('role',50)->nullable();
            // $table->primary('nus_id');
        });

        // Schema::table('users', function(Blueprint $table)
        // {
        //     $table->primary(['id', 'nus_id']);
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
