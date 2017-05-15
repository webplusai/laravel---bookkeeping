<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'bill_item', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->integer( 'bill_id' )->unsigned();
            $table->smallInteger( 'rank' )->unsigned();
            $table->integer( 'product_service_id' )->unsigned();
            $table->text( 'description' )->nullable();
            $table->integer( 'qty' )->unsinged();
            $table->double( 'rate' );
            $table->double( 'amount' );
            $table->timestamps();

            $table->foreign( 'bill_id' )->references( 'id' )->on( 'bill' );
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
        Schema::dropIfExists('bill_item');
    }
}
