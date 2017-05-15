<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapSupplierCreditBillPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'map_supplier_credit_bill_payment', function ( Blueprint $table ) {
            $table->increments( 'id' );
            $table->integer( 'supplier_credit_id' )->unsigned();
            $table->integer( 'bill_payment_id' )->unsigned();
            $table->integer( 'payment' );
            $table->timestamps();

            $table->foreign( 'supplier_credit_id' )->references( 'id' )->on( 'supplier_credit' );
            $table->foreign( 'bill_payment_id' )->references( 'id' )->on( 'bill_payment' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'map_supplier_credit_bill_payment' );
    }
}
