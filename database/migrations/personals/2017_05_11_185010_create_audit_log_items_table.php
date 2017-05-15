<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditLogItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'audit_log_item', function ( Blueprint $table ) {
            $table->increments( 'id' );
            $table->integer( 'audit_log_id' )->unsigned();
            $table->integer( 'no' )->unsigned();
            $table->integer( 'customer_id' )->unsigned()->nullable();
            $table->integer( 'supplier_id' )->unsigned()->nullable();
            $table->integer( 'product_service_id' )->unsigned()->nullable();
            $table->text( 'description' )->nullable();
            $table->double( 'qty' )->nullable();
            $table->double( 'rate' )->nullable();
            $table->integer( 'account_id' )->unsigned()->nullable();
            $table->double( 'amount' );
            $table->double( 'open_balance' )->nullable();
            $table->timestamps();

            $table->foreign( 'audit_log_id' )->references( 'id' )->on( 'audit_log' );
            $table->foreign( 'customer_id' )->references( 'id' )->on( 'customer' );
            $table->foreign( 'supplier_id' )->references( 'id' )->on( 'supplier' );
            $table->foreign( 'product_service_id' )->references( 'id' )->on( 'product_service' );
            $table->foreign( 'account_id' )->references( 'id' )->on( 'account' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'audit_log_item' );
    }
}
