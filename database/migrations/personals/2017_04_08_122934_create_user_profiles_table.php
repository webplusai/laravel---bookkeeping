<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'user_profile', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->text( 'name' );
            $table->text( 'email' )->nullable();
            $table->boolean( 'email_verified' )->nullable();
            $table->text( 'mobile' )->nullable();
            $table->boolean( 'mobile_verified' )->nullable();
            $table->text( 'password' )->nullable();
            $table->text( 'address' )->nullable();
            $table->text( 'city' )->nullable();
            $table->text( 'country' )->nullable();
            $table->boolean( 'is_trash' )->default( 0 );
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
        Schema::dropIfExists('user_profile');
    }
}
