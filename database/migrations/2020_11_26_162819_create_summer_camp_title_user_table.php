<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSummerCampTitleUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('summer_camp_title_user', function (Blueprint $table) {
            $table->timestamps();
            $table->bigInteger('summer_camp_title_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('summer_camp_title_id')->references('id')->on('summer_camp_titles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->primary(['summer_camp_title_id','user_id']);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('summer_camp_title_user');
    }
}
