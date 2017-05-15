<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'company_profile', function ( Blueprint $table ) {
            $table->increments( 'id' )->unsigned();
            $table->text( 'company_name' )->nullable();
            $table->integer( 'business_id_no' )->unsigned()->nullable();
            $table->text( 'industry' )->nullable();
            $table->text( 'company_email' )->nullable();
            $table->text( 'company_phone' )->nullable();
            $table->text( 'company_website' )->nullable();
            $table->text( 'address' )->nullable();
            $table->text( 'city' )->nullable();
            $table->text( 'country' )->nullable();
            $table->text( 'company_logo' )->nullable();
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
        Schema::dropIfExists('company_profile');
    }
}
