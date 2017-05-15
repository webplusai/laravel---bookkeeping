<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'payment', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->integer( 'sales_id' )->unsigned();
            $table->integer( 'account_id' )->unsigned();
            $table->text( 'note' )->nullable();
            $table->timestamps();

            $table->foreign( 'account_id' )->references( 'id' )->on( 'account' );
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
        Schema::dropIfExists('payment');
    }
}
