<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesReceiptItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'sales_receipt_item', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->integer( 'sales_receipt_id' )->unsigned()->nullable();
            $table->smallInteger( 'rank' )->unsigned()->nullable();
            $table->tinyInteger( 'item_type' )->unsigned()->nullable();
            $table->integer( 'product_service_id' )->unsigned()->nullable();
            $table->text( 'description' )->nullable();
            $table->integer( 'qty' )->unsigned()->nullable();
            $table->double( 'rate' )->nullable();
            $table->double( 'amount' )->nullable();
            $table->timestamps();

            $table->foreign( 'product_service_id' )->references( 'id' )->on( 'product_service' );
            $table->foreign( 'sales_receipt_id' )->references( 'id' )->on( 'sales_receipt' )->onDelete( 'cascade' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_receipt_item');
    }
}
