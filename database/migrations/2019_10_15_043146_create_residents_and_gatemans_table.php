<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResidentsAndGatemansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('residents_and_gatemans', function (Blueprint $table) {
            $table->unsignedBigInteger('resident_id');  //references users table with role = 1
            $table->unsignedBigInteger('gateman_id');   //references users table with role = 2
            $table->timestamps();

            $table->foreign('resident_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('gateman_id')->references('id')->on('users')->onDelete('cascade');

        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('residents_and_gatemans');
    }
}
