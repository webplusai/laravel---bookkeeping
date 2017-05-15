<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'product_service', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->string( 'name', 255 );
            $table->string( 'sku', 255 );
            $table->double( 'selling_price' );
            $table->integer( 'product_category_id' )->unsigned();
            $table->double( 'purchase_price' );
            $table->tinyInteger( 'item_type' )->unsigned();
            $table->boolean( 'is_inventoriable' );
            $table->boolean( 'is_active' );
            $table->boolean( 'is_trash' )->default( 0 );
            $table->timestamps();

            $table->foreign( 'product_category_id' )->references( 'id' )->on( 'product_category' )->onDelete( 'cascade' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_service');
    }
}
