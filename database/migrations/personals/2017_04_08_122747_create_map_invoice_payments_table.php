<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapInvoicePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'map_invoice_payment', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned;
            $table->integer( 'invoice_id' )->unsigned();
            $table->integer( 'payment_id' )->unsigned();
            $table->double( 'payment' );
            $table->timestamps();

            $table->foreign( 'invoice_id' )->references( 'id' )->on( 'invoice' )->onDelete( 'cascade' );
            $table->foreign( 'payment_id' )->references( 'id' )->on( 'payment' )->onDelete( 'cascade' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('map_invoice_payment');
    }
}
