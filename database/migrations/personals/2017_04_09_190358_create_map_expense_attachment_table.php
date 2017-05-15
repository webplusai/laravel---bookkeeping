<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapExpenseAttachmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map_expense_attachment', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer( 'expense_id' )->unsigned();
            $table->integer( 'attachment_id' )->unsigned();
            $table->timestamps();

            $table->foreign( 'expense_id' )->references( 'id' )->on( 'expense' );
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
        Schema::dropIfExists('map_expense_attachment');
    }
}
