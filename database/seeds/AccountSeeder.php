<?php

use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table( 'account' )->truncate();
    
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 1,  'account_detail_type_id' => 1,   'account_number' => 101,  'balance' => 0,  'name' => 'Cash',                                     'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 1,  'account_detail_type_id' => 2,   'account_number' => 120,  'balance' => 0,  'name' => 'Accounts Receivable',                      'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 1,  'account_detail_type_id' => 3,   'account_number' => 130,  'balance' => 0,  'name' => 'Allowance for Bad Debts' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 1,  'account_detail_type_id' => 4,   'account_number' => 140,  'balance' => 0,  'name' => 'Merchandise Inventory',                    'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 1,  'account_detail_type_id' => 5,   'account_number' => 150,  'balance' => 0,  'name' => 'Supplies',                                 'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 1,  'account_detail_type_id' => 6,   'account_number' => 160,  'balance' => 0,  'name' => 'Prepaid Insurance',                        'Description' => '' ] );
        
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 2,  'account_detail_type_id' => 7,   'account_number' => 170,  'balance' => 0,  'name' => 'Land',                                     'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 2,  'account_detail_type_id' => 8,   'account_number' => 175,  'balance' => 0,  'name' => 'Buildings',                                'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 2,  'account_detail_type_id' => 9,   'account_number' => 178,  'balance' => 0,  'name' => 'Accumulated Depreciation - Buildings',     'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 2,  'account_detail_type_id' => 10,  'account_number' => 180,  'balance' => 0,  'name' => 'Equipment',                                'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 2,  'account_detail_type_id' => 11,  'account_number' => 188,  'balance' => 0,  'name' => 'Accumulated Depreciation - Equipment',     'Description' => '' ] );

        DB::table( 'account' )->insert( [ 'account_category_type_id' => 3,  'account_detail_type_id' => 12,  'account_number' => 210,  'balance' => 0,  'name' => 'Notes Payable',                            'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 3,  'account_detail_type_id' => 13,  'account_number' => 215,  'balance' => 0,  'name' => 'Accounts Payable',                         'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 3,  'account_detail_type_id' => 14,  'account_number' => 220,  'balance' => 0,  'name' => 'Wages Payable',                            'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 3,  'account_detail_type_id' => 15,  'account_number' => 230,  'balance' => 0,  'name' => 'Interest Payable',                         'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 3,  'account_detail_type_id' => 16,  'account_number' => 240,  'balance' => 0,  'name' => 'Unearned Revenues',                        'Description' => '' ] );

        DB::table( 'account' )->insert( [ 'account_category_type_id' => 4,  'account_detail_type_id' => 17,  'account_number' => 250,  'balance' => 0,  'name' => 'Mortgage Loan Payable',                    'Description' => '' ] );

        DB::table( 'account' )->insert( [ 'account_category_type_id' => 5,  'account_detail_type_id' => 18,  'account_number' => 290,  'balance' => 0,  'name' => 'Owners Capital',                           'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 5,  'account_detail_type_id' => 19,  'account_number' => 295,  'balance' => 0,  'name' => 'Owners Drawing',                           'Description' => '' ] );

        DB::table( 'account' )->insert( [ 'account_category_type_id' => 6,  'account_detail_type_id' => 20,  'account_number' => 310,  'balance' => 0,  'name' => 'Service Revenues',                         'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 6,  'account_detail_type_id' => 21,  'account_number' => 320,  'balance' => 0,  'name' => 'Sales Revenues',                           'Description' => '' ] );

        DB::table( 'account' )->insert( [ 'account_category_type_id' => 7,  'account_detail_type_id' => 22,  'account_number' => 500,  'balance' => 0,  'name' => 'Salaries Expense',                         'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 7,  'account_detail_type_id' => 23,  'account_number' => 510,  'balance' => 0,  'name' => 'Wages Expense',                            'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 7,  'account_detail_type_id' => 24,  'account_number' => 520,  'balance' => 0,  'name' => 'Supplies Expense',                         'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 7,  'account_detail_type_id' => 25,  'account_number' => 530,  'balance' => 0,  'name' => 'Rent Expense',                             'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 7,  'account_detail_type_id' => 26,  'account_number' => 540,  'balance' => 0,  'name' => 'Utilities Expense',                        'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 7,  'account_detail_type_id' => 27,  'account_number' => 550,  'balance' => 0,  'name' => 'Postage and Communications',               'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 7,  'account_detail_type_id' => 28,  'account_number' => 560,  'balance' => 0,  'name' => 'Advertising Expense',                      'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 7,  'account_detail_type_id' => 29,  'account_number' => 570,  'balance' => 0,  'name' => 'Depreciation Expense',                     'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 7,  'account_detail_type_id' => 30,  'account_number' => 580,  'balance' => 0,  'name' => 'Bad Debts Expense',                        'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 7,  'account_detail_type_id' => 31,  'account_number' => 590,  'balance' => 0,  'name' => 'Purchases',                                'Description' => '' ] );

        DB::table( 'account' )->insert( [ 'account_category_type_id' => 8,  'account_detail_type_id' => 32,  'account_number' => 610,  'balance' => 0,  'name' => 'Cost of Sales/Services',                   'Description' => '' ] );

        DB::table( 'account' )->insert( [ 'account_category_type_id' => 9,  'account_detail_type_id' => 33,  'account_number' => 710,  'balance' => 0,  'name' => 'Interest Revenues',                        'Description' => '' ] );
        DB::table( 'account' )->insert( [ 'account_category_type_id' => 9,  'account_detail_type_id' => 34,  'account_number' => 720,  'balance' => 0,  'name' => 'Gain on Sale of Assets',                   'Description' => '' ] );

        DB::table( 'account' )->insert( [ 'account_category_type_id' => 10, 'account_detail_type_id' => 35,  'account_number' => 810,  'balance' => 0,  'name' => 'Loss on Sale of Assets',                   'Description' => '' ] );
    }
}
