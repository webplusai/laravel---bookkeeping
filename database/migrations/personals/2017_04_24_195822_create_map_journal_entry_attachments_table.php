<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapJournalEntryAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'map_journal_entry_attachment', function ( Blueprint $table ) {
            $table->increments( 'id' );
            $table->integer( 'journal_entry_id' )->unsigned();
            $table->integer( 'attachment_id' )->unsigned();
            $table->timestamps();

            $table->foreign( 'journal_entry_id' )->references( 'id' )->on( 'journal_entry' );
            $table->foreign( 'attachment_id' )->references( 'id' )->on( 'attachment' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'map_journal_entry_attachment' );
    }
}
