<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJournalEntryItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'journal_entry_item', function ( Blueprint $table ) {
            $table->increments( 'id' );
            $table->smallInteger( 'rank' );
            $table->integer( 'journal_entry_id' )->unsigned();
            $table->integer( 'account_id' )->unsigned();
            $table->double( 'debits' )->nullable();
            $table->double( 'credits' )->nullable();
            $table->text( 'description' )->nullable();
            $table->integer( 'person_id' )->nullable();
            $table->integer( 'person_type' )->nullable();
            $table->timestamps();

            $table->foreign( 'account_id' )->references( 'id' )->on( 'account' );
            $table->foreign( 'journal_entry_id' )->references( 'id' )->on( 'journal_entry' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journal_entry_item');
    }
}
