<?php

use Illuminate\Database\Seeder;

class AccountCategoryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table( 'account_category_type' )->truncate();
    
        DB::table( 'account_category_type' )->insert( [ 'name' => 'Current Asset' ] );
        DB::table( 'account_category_type' )->insert( [ 'name' => 'Non-Current Asset' ] );
        DB::table( 'account_category_type' )->insert( [ 'name' => 'Current Liability' ] );
        DB::table( 'account_category_type' )->insert( [ 'name' => 'Non-Current Liability' ] );
        DB::table( 'account_category_type' )->insert( [ 'name' => 'Owner\'s Equity' ] );
        DB::table( 'account_category_type' )->insert( [ 'name' => 'Operating Revenue' ] );
        DB::table( 'account_category_type' )->insert( [ 'name' => 'Operating Expense' ] );
        DB::table( 'account_category_type' )->insert( [ 'name' => 'Cost of Sales/Services'] );
        DB::table( 'account_category_type' )->insert( [ 'name' => 'Non-Operating Revenues and Gains' ] );
        DB::table( 'account_category_type' )->insert( [ 'name' => 'Non-Operating Expenses and Losses' ] );
    }
}
