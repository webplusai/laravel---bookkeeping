<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupplierCreditAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'supplier_credit_account', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->integer( 'supplier_credit_id' )->unsigned()->nullable();
            $table->smallInteger( 'rank' )->unsigned()->nullable();
            $table->integer( 'account_id' )->unsigned()->nullable();
            $table->text( 'description' )->nullable()->nullable();
            $table->double( 'amount' )->nullable();
            $table->timestamps();

            $table->foreign( 'account_id' )->references( 'id' )->on( 'account' );
            $table->foreign( 'supplier_credit_id' )->references( 'id' )->on( 'supplier_credit' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'supplier_credit_account' );
    }
}
