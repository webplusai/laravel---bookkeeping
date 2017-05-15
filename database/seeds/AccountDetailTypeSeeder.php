<?php

use Illuminate\Database\Seeder;

class AccountDetailTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	  DB::table( 'account_detail_type' )->truncate();
    
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 1, 'name' => 'Cash' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 1, 'name' => 'Accounts Receivable' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 1, 'name' => 'Allowance for Bad Debts' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 1, 'name' => 'Merchandise Inventory' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 1, 'name' => 'Supplies' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 1, 'name' => 'Prepaid Insurance' ] );

        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 2, 'name' => 'Land' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 2, 'name' => 'Buildings' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 2, 'name' => 'Accumulated Depreciation - Buildings' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 2, 'name' => 'Equipment' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 2, 'name' => 'Accumulated Depreciation - Equipment' ] );

        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 3, 'name' => 'Notes Payable' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 3, 'name' => 'Accounts Payable' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 3, 'name' => 'Wages Payable' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 3, 'name' => 'Interest Payable' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 3, 'name' => 'Unearned Revenues' ] );

        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 4, 'name' => 'Mortgage Loan Payable' ] );

        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 5, 'name' => 'Owners Capital' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 5, 'name' => 'Owners Drawing' ] );

        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 6, 'name' => 'Service Revenues' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 6, 'name' => 'Sales Revenues' ] );

        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 7, 'name' => 'Salaries Expense' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 7, 'name' => 'Wages Expense' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 7, 'name' => 'Supplies Expense' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 7, 'name' => 'Rent Expense' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 7, 'name' => 'Utilities Expense' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 7, 'name' => 'Postage and Communications' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 7, 'name' => 'Advertising Expense' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 7, 'name' => 'Depreciation Expense' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 7, 'name' => 'Bad Debts Expense' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 7, 'name' => 'Purchases' ] );

        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 8, 'name' => 'Cost of Sales/Services' ] );

        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 9, 'name' => 'Interest Revenues' ] );
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 9, 'name' => 'Gain on Sale of Assets' ] );
        
        DB::table( 'account_detail_type' )->insert( [ 'account_category_type_id' => 10, 'name' => 'Loss on Sale of Assets' ] );
    }
}
