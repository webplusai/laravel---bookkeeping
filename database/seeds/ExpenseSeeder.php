<?php

use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('expense')->truncate();

        // Type: 1 - Expense
        DB::table('expense')->insert( [ 'date' => '2016-10-15', 'transaction_type' => 1, 'payee_id' => 1, 'account_id' => '5', 'total' => 300, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2016-10-20', 'transaction_type' => 1, 'payee_id' => 1, 'account_id' => '13', 'total' => 1500, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2016-10-21', 'transaction_type' => 1, 'payee_id' => 1, 'account_id' => '130', 'total' => 200, 'statement_memo' => 'Memo' ] );

        DB::table('expense')->insert( [ 'date' => '2016-10-26', 'transaction_type' => 1, 'payee_id' => 2, 'account_id' => '45', 'total' => 300, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2016-10-29', 'transaction_type' => 1, 'payee_id' => 2, 'account_id' => '77', 'total' => 200, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2016-10-30', 'transaction_type' => 1, 'payee_id' => 2, 'account_id' => '86', 'total' => 200, 'statement_memo' => 'Memo' ] );

        DB::table('expense')->insert( [ 'date' => '2016-11-03', 'transaction_type' => 1, 'payee_id' => 3, 'account_id' => '13', 'total' => 500, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2016-11-06', 'transaction_type' => 1, 'payee_id' => 3, 'account_id' => '86', 'total' => 600, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2016-11-12', 'transaction_type' => 1, 'payee_id' => 3, 'account_id' => '86', 'total' => 100, 'statement_memo' => 'Memo' ] );

        DB::table('expense')->insert( [ 'date' => '2016-11-17', 'transaction_type' => 1, 'payee_id' => 4, 'account_id' => '77', 'total' => 900, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2016-11-27', 'transaction_type' => 1, 'payee_id' => 4, 'account_id' => '130', 'total' => 500, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2016-11-29', 'transaction_type' => 1, 'payee_id' => 4, 'account_id' => '77', 'total' => 500, 'statement_memo' => 'Memo' ] );

        DB::table('expense')->insert( [ 'date' => '2016-12-04', 'transaction_type' => 1, 'payee_id' => 5, 'account_id' => '77', 'total' => 1200, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2016-12-13', 'transaction_type' => 1, 'payee_id' => 5, 'account_id' => '45', 'total' => 700, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2016-12-19', 'transaction_type' => 1, 'payee_id' => 5, 'account_id' => '86', 'total' => 800, 'statement_memo' => 'Memo' ] );

        DB::table('expense')->insert( [ 'date' => '2016-12-31', 'transaction_type' => 1, 'payee_id' => 6, 'account_id' => '130', 'total' => 900, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2017-01-13', 'transaction_type' => 1, 'payee_id' => 6, 'account_id' => '45', 'total' => 1500, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2017-01-17', 'transaction_type' => 1, 'payee_id' => 6, 'account_id' => '13', 'total' => 2400, 'statement_memo' => 'Memo' ] );

        DB::table('expense')->insert( [ 'date' => '2017-01-24', 'transaction_type' => 1, 'payee_id' => 7, 'account_id' => '5', 'total' => 600, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2017-01-24', 'transaction_type' => 1, 'payee_id' => 7, 'account_id' => '13', 'total' => 700, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2017-01-27', 'transaction_type' => 1, 'payee_id' => 7, 'account_id' => '5', 'total' => 100, 'statement_memo' => 'Memo' ] );

        DB::table('expense')->insert( [ 'date' => '2017-02-05', 'transaction_type' => 1, 'payee_id' => 8, 'account_id' => '5', 'total' => 200, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2017-02-06', 'transaction_type' => 1, 'payee_id' => 8, 'account_id' => '77', 'total' => 400, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2017-02-11', 'transaction_type' => 1, 'payee_id' => 8, 'account_id' => '13', 'total' => 600, 'statement_memo' => 'Memo' ] );

        DB::table('expense')->insert( [ 'date' => '2017-02-15', 'transaction_type' => 1, 'payee_id' => 9, 'account_id' => '13', 'total' => 800, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2017-02-22', 'transaction_type' => 1, 'payee_id' => 9, 'account_id' => '86', 'total' => 200, 'statement_memo' => 'Memo' ] );
        DB::table('expense')->insert( [ 'date' => '2017-02-28', 'transaction_type' => 1, 'payee_id' => 9, 'account_id' => '130', 'total' => 200, 'statement_memo' => 'Memo' ] );
    }
}
