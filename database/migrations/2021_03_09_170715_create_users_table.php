<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
          $table->id();
          $table->string('name');
          $table->string('surname');
          $table->string('email')->unique();
          $table->timestamp('email_verified_at')->nullable();
          $table->string('password');
          $table->rememberToken();
          $table->date('birth_date')->nullable();
          $table->string('actual_position')->nullable();
          $table->string('address_street')->nullable();
          $table->string('address_number')->nullable();
          $table->string('address_city')->nullable();
          $table->string('address_cp')->nullable();
          $table->string('address_country')->nullable();
          $table->string('phone')->nullable();
          $table->string('dni')->nullable();
          $table->boolean('deactivated')->default(false);
          $table->string('social_security_number')->nullable();
          $table->string('bank_account')->nullable();
          $table->string('avatar_path')->nullable();
          $table->string('dni_path')->nullable();
          $table->string('sex_offense_certificate_path')->nullable();
          $table->string('cv_path')->nullable();
          $table->bigInteger('user_type_id')->unsigned();
          $table->foreign('user_type_id')->references('id')->on('user_types');
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
      Schema::dropIfExists('users');
  }
}
