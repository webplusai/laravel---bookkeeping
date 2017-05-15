<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_payment', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer( 'expenses_id' )->unsigned();
            $table->integer( 'account_id' )->unsigned();
            $table->text( 'note' )->nullable();
            $table->timestamps();

            $table->foreign( 'account_id')->references( 'id' )->on( 'account' );
            $table->foreign( 'expenses_id' )->references( 'id' )->on( 'expenses' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bill_payment');
    }
}
