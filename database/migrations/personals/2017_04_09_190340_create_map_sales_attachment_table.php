<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapSalesAttachmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map_sales_attachment', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer( 'sales_id' )->unsigned();
            $table->integer( 'attachment_id' )->unsigned();
            $table->timestamps();

            $table->foreign( 'sales_id' )->references( 'id' )->on( 'sales' );
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
        Schema::dropIfExists('map_sales_attachment');
    }
}
