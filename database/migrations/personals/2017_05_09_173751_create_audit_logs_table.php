<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'audit_log', function ( Blueprint $table ) {
            $table->increments( 'id' );
            $table->integer( 'table_id' )->unsigned();
            $table->integer( 'record_id' )->unsigned();
            $table->integer( 'trxn_id' )->unsigned();
            $table->datetime( 'date_changed' );
            $table->string( 'user_email', 255 );
            $table->text( 'event_text' )->nullable();
            $table->text( 'target_name' )->nullable();
            $table->integer( 'person_id' )->nullable();
            $table->integer( 'person_type' )->nullable();
            $table->date( 'date' )->nullable();
            $table->double( 'amount' )->nullable();
            $table->double( 'open_balance' )->nullable();
            $table->text( 'message' )->nullable();
            $table->text( 'memo' )->nullable();
            $table->boolean( 'is_indirect' )->nullable();
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
        Schema::dropIfExists( 'audit_log' );
    }
}
