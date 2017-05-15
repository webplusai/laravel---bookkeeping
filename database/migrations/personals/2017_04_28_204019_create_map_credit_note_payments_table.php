<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapCreditNotePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'map_credit_note_payment', function ( Blueprint $table ) {
            $table->increments('id');
            $table->integer( 'credit_note_id' )->unsigned();
            $table->integer( 'payment_id' )->unsigned();
            $table->double( 'payment' );
            $table->timestamps();

            $table->foreign( 'credit_note_id' )->references( 'id' )->on( 'credit_note' );
            $table->foreign( 'payment_id' )->references( 'id' )->on( 'payment' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'map_credit_note_payment' );
    }
}
