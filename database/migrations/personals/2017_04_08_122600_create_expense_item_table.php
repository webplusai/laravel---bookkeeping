<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpenseItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'expense_item', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->integer( 'expense_id' )->unsigned();
            $table->smallInteger( 'rank' )->unsigned();
            $table->integer( 'product_service_id' )->unsigned();
            $table->text( 'description' )->nullable();
            $table->integer( 'qty' )->unsigned();
            $table->double( 'rate' );
            $table->double( 'amount' );
            $table->timestamps();

            $table->foreign( 'product_service_id' )->references( 'id' )->on( 'product_service' );
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
        Schema::dropIfExists('expense_item');
    }
}
