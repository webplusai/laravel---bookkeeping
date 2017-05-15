<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'bill_account', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->integer( 'bill_id' )->unsigned()->nullable();
            $table->smallInteger( 'rank' )->unsigned()->nullable();
            $table->integer( 'account_id' )->unsigned()->nullable();
            $table->text( 'description' )->nullable()->nullable();
            $table->double( 'amount' )->nullable();
            $table->timestamps();

            $table->foreign( 'account_id' )->references( 'id' )->on( 'account' );
            $table->foreign( 'bill_id' )->references( 'id' )->on( 'bill' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'bill_account' );
    }
}
