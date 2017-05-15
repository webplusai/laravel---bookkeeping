<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditNoteItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'credit_note_item', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->integer( 'credit_note_id' )->unsigned()->nullable();
            $table->smallInteger( 'rank' )->unsigned()->nullable();
            $table->tinyInteger( 'item_type' )->unsigned()->nullable();
            $table->integer( 'product_service_id' )->unsigned()->nullable();
            $table->text( 'description' )->nullable();
            $table->integer( 'qty' )->unsigned()->nullable();
            $table->double( 'rate' )->nullable();
            $table->double( 'amount' )->nullable();
            $table->timestamps();

            $table->foreign( 'product_service_id' )->references( 'id' )->on( 'product_service' );
            $table->foreign( 'credit_note_id' )->references( 'id' )->on( 'credit_note' )->onDelete( 'cascade' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_note_items');
    }
}
