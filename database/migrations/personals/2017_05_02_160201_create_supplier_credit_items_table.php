<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupplierCreditItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'supplier_credit_item', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->integer( 'supplier_credit_id' )->unsigned();
            $table->smallInteger( 'rank' )->unsigned();
            $table->integer( 'product_service_id' )->unsigned();
            $table->text( 'description' )->nullable();
            $table->integer( 'qty' )->unsinged();
            $table->double( 'rate' );
            $table->double( 'amount' );
            $table->timestamps();

            $table->foreign( 'supplier_credit_id' )->references( 'id' )->on( 'supplier_credit' );
            $table->foreign( 'product_service_id' )->references( 'id' )->on( 'product_service' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'supplier_credit_item' );
    }
}
