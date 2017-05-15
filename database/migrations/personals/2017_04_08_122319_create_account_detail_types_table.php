<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountDetailTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'account_detail_type', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->integer( 'account_category_type_id' )->unsigned();
            $table->string( 'name', 50 );
            $table->text( 'description' )->nullable();
            $table->boolean( 'is_trash' )->default( 0 );
            $table->timestamps();

            $table->foreign( 'account_category_type_id' )->references( 'id' )->on( 'account_category_type' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_detail_type');
    }
}
