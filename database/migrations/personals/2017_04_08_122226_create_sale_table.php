<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'sales', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->date( 'date' );
            $table->tinyInteger( 'transaction_type' )->unsigned();
            $table->integer( 'invoice_receipt_no' )->unsigned()->nullable();
            $table->integer( 'customer_id' )->unsigned();
            $table->date( 'due_date' )->nullable();
            $table->double( 'total' );
            $table->double( 'balance' );
            $table->tinyInteger( 'status' )->unsigned();
            $table->boolean( 'is_trash' )->default( 0 );
            $table->timestamps();

            $table->foreign( 'customer_id' )->references( 'id' )->on( 'customer' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
