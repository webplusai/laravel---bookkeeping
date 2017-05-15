<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapBillBillPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map_bill_bill_payment', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer( 'bill_id' )->unsigned();
            $table->integer( 'bill_payment_id' )->unsigned();
            $table->double( 'payment' );
            $table->timestamps();

            $table->foreign( 'bill_id' )->references( 'id' )->on( 'bill' );
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
        Schema::dropIfExists('map_bill_bill_payment');
    }
}
