<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpenseAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'expense_account', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->integer( 'expense_id' )->unsigned()->nullable();
            $table->smallInteger( 'rank' )->unsigned()->nullable();
            $table->integer( 'account_id' )->unsigned()->nullable();
            $table->text( 'description' )->nullable();
            $table->double( 'amount' )->nullable();
            $table->timestamps();

            $table->foreign( 'account_id' )->references( 'id' )->on( 'account' );
            $table->foreign( 'expense_id' )->references( 'id' )->on( 'expense' )->onDelete( 'cascade' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expense_account');
    }
}
