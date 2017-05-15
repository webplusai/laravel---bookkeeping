<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'customer', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->string( 'name', 255 );
            $table->string( 'company', 255 )->nullable();
            $table->string( 'email', 255 )->nullable();
            $table->string( 'country', 255 )->nullable();
            $table->string( 'city', 255 )->nullable();
            $table->string( 'phone', 25 )->nullable();
            $table->string( 'address1', 255 )->nullable();
            $table->string( 'address2', 255 )->nullable();
            $table->text( 'note' )->nullable();
            $table->double( 'balance' )->nullable();
            $table->boolean( 'is_active' );
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
        Schema::dropIfExists('customer');
    }
}
