<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'account', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->string( 'name', 50 );
            $table->text( 'description' )->nullable();
            $table->integer( 'account_category_type_id' )->unsigned();
            $table->integer( 'account_detail_type_id' )->unsigned();
            $table->integer( 'account_number' )->unsigned();
            $table->double( 'balance' )->nullable();
            $table->boolean( 'is_trash' )->default( 0 );
            $table->timestamps();

            $table->foreign( 'account_category_type_id' )->references( 'id' )->on( 'account_category_type' );
            $table->foreign( 'account_detail_type_id' )->references( 'id' )->on( 'account_detail_type' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account');
    }
}
