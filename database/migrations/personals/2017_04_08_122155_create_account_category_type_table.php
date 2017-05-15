<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountCategoryTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'account_category_type', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->string( 'name', 255 );
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
        Schema::dropIfExists('account_category_type');
    }
}
