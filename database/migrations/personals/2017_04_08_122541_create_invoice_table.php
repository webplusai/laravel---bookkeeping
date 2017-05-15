<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'invoice', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->integer( 'sales_id' )->unsigned();
            $table->text( 'message' )->nullable();
            $table->text( 'statement_memo' )->nullable();
            $table->tinyInteger( 'discount_type_id' )->unsigned();
            $table->double( 'discount_amount' )->nullable();
            $table->double( 'sub_total' );
            $table->double( 'shipping' )->nullable();
            $table->double( 'deposit' )->nullable();
            $table->timestamps();

            $table->foreign( 'sales_id' )->references( 'id' )->on( 'sales' )->onDelete( 'cascade' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice');
    }
}
