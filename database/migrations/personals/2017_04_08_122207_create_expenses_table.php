<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'expenses', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->date( 'date' );
            $table->tinyInteger( 'transaction_type' )->unsigned();
            $table->integer( 'payee_id' )->unsigned();
            $table->tinyInteger( 'payee_type' )->unsigned();
            $table->integer( 'account_id' )->unsigned();
            $table->date( 'due_date' )->nullable();
            $table->double( 'total' );
            $table->double( 'balance' );
            $table->tinyInteger( 'status' )->unsigned();
            $table->boolean( 'is_trash' )->default( 0 );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}
