<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJournalEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'journal_entry', function ( Blueprint $table ) {
            $table->increments( 'id' );
            $table->date( 'date' );
            $table->text( 'statement_memo' );
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
        Schema::dropIfExists('journal_entry');
    }
}
