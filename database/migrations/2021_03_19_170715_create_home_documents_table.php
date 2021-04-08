<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHomeDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('home_documents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('home_post_id')->unsigned();
            $table->foreign('home_post_id')->references('id')->on('home_posts');
            $table->string('original_name');
            $table->string('path');
            $table->unique(['home_post_id','path']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('home_documents');
    }
}
