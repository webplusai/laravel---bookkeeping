<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupplierCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'supplier_credit', function ( Blueprint $table ) {
            $table->increments( 'id' );
            $table->integer( 'expenses_id' )->unsigned();
            $table->text( 'statement_memo' )->nullable();
            $table->timestamps();

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
        Schema::dropIfExists( 'supplier_credit' );
    }
}
