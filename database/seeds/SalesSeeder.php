<?php

use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sales')->truncate();

        // Type: 1 - Invoice, 2 - Payment
        // Status: if (Type == 1) { 1 - Unpaid, 2 - Paid } else if (Type == 2) { 1 - Open, 2 - Closed }
        DB::table('sales')->insert( [ 'date' => '2016-09-30', 'transaction_type' => 1, 'transaction_id' => 1, 'customer_id' => 1, 'due_date' => '2016-10-06', 'total' => 500, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2016-10-01', 'transaction_type' => 2, 'transaction_id' => 1, 'customer_id' => 1, 'due_date' => '2016-10-06', 'total' => 500, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2016-10-02', 'transaction_type' => 1, 'transaction_id' => 2, 'customer_id' => 1, 'due_date' => '2016-11-20', 'total' => 300, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2016-10-07', 'transaction_type' => 2, 'transaction_id' => 2, 'customer_id' => 1, 'due_date' => '2016-11-20', 'total' => 300, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2016-10-15', 'transaction_type' => 1, 'transaction_id' => 3, 'customer_id' => 1, 'due_date' => '2016-11-30', 'total' => 500, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2016-10-15', 'transaction_type' => 2, 'transaction_id' => 3, 'customer_id' => 1, 'due_date' => '2016-11-30', 'total' => 500, 'status' => '2' ] );

        DB::table('sales')->insert( [ 'date' => '2016-10-20', 'transaction_type' => 1, 'transaction_id' => 4, 'customer_id' => 2, 'due_date' => '2016-10-27', 'total' => 600, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2016-10-29', 'transaction_type' => 2, 'transaction_id' => 4, 'customer_id' => 2, 'due_date' => '2016-10-27', 'total' => 600, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2016-10-28', 'transaction_type' => 1, 'transaction_id' => 5, 'customer_id' => 2, 'due_date' => '2016-12-25', 'total' => 500, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2016-11-01', 'transaction_type' => 2, 'transaction_id' => 5, 'customer_id' => 2, 'due_date' => '2016-12-25', 'total' => 500, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2016-11-12', 'transaction_type' => 1, 'transaction_id' => 6, 'customer_id' => 2, 'due_date' => '2016-11-30', 'total' => 500, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2016-12-03', 'transaction_type' => 2, 'transaction_id' => 6, 'customer_id' => 2, 'due_date' => '2016-11-30', 'total' => 500, 'status' => '2' ] );

        DB::table('sales')->insert( [ 'date' => '2016-11-27', 'transaction_type' => 1, 'transaction_id' => 7, 'customer_id' => 3, 'due_date' => '2016-12-06', 'total' => 700, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2016-12-08', 'transaction_type' => 2, 'transaction_id' => 7, 'customer_id' => 3, 'due_date' => '2016-12-06', 'total' => 700, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2016-12-20', 'transaction_type' => 1, 'transaction_id' => 8, 'customer_id' => 3, 'due_date' => '2016-12-30', 'total' => 1200, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2017-01-03', 'transaction_type' => 2, 'transaction_id' => 8, 'customer_id' => 3, 'due_date' => '2016-12-30', 'total' => 1200, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2016-12-30', 'transaction_type' => 1, 'transaction_id' => 9, 'customer_id' => 3, 'due_date' => '2017-01-06', 'total' => 500, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2017-01-03', 'transaction_type' => 2, 'transaction_id' => 9, 'customer_id' => 3, 'due_date' => '2017-01-06', 'total' => 500, 'status' => '2' ] );

        DB::table('sales')->insert( [ 'date' => '2017-01-05', 'transaction_type' => 1, 'transaction_id' => 10, 'customer_id' => 4, 'due_date' => '2017-01-09', 'total' => 800, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2017-01-07', 'transaction_type' => 2, 'transaction_id' => 10, 'customer_id' => 4, 'due_date' => '2017-01-09', 'total' => 800, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2017-01-08', 'transaction_type' => 1, 'transaction_id' => 11, 'customer_id' => 4, 'due_date' => '2017-01-14', 'total' => 100, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2017-01-16', 'transaction_type' => 2, 'transaction_id' => 11, 'customer_id' => 4, 'due_date' => '2017-01-14', 'total' => 100, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2017-01-19', 'transaction_type' => 1, 'transaction_id' => 12, 'customer_id' => 4, 'due_date' => '2017-01-30', 'total' => 2000, 'status' => '2' ] );
        DB::table('sales')->insert( [ 'date' => '2017-01-20', 'transaction_type' => 2, 'transaction_id' => 12, 'customer_id' => 4, 'due_date' => '2017-01-30', 'total' => 2000, 'status' => '2' ] );

        DB::table('sales')->insert( [ 'date' => '2017-01-22', 'transaction_type' => 1, 'transaction_id' => 13, 'customer_id' => 5, 'due_date' => '2017-01-30', 'total' => 500, 'status' => '1' ] );
        DB::table('sales')->insert( [ 'date' => '2017-01-25', 'transaction_type' => 1, 'transaction_id' => 14, 'customer_id' => 5, 'due_date' => '2017-02-06', 'total' => 2500, 'status' => '1' ] );
        DB::table('sales')->insert( [ 'date' => '2017-01-30', 'transaction_type' => 1, 'transaction_id' => 15, 'customer_id' => 5, 'due_date' => '2017-02-09', 'total' => 2500, 'status' => '1' ] );

        DB::table('sales')->insert( [ 'date' => '2017-02-01', 'transaction_type' => 1, 'transaction_id' => 16, 'customer_id' => 6, 'due_date' => '2017-02-07', 'total' => 3000, 'status' => '1' ] );
        DB::table('sales')->insert( [ 'date' => '2017-02-03', 'transaction_type' => 1, 'transaction_id' => 17, 'customer_id' => 6, 'due_date' => '2017-02-12', 'total' => 1500, 'status' => '1' ] );
        DB::table('sales')->insert( [ 'date' => '2017-02-06', 'transaction_type' => 1, 'transaction_id' => 18, 'customer_id' => 6, 'due_date' => '2017-02-20', 'total' => 800, 'status' => '1' ] );

        DB::table('sales')->insert( [ 'date' => '2017-02-06', 'transaction_type' => 1, 'transaction_id' => 20, 'customer_id' => 7, 'due_date' => '2017-02-08', 'total' => 700, 'status' => '1' ] );
        DB::table('sales')->insert( [ 'date' => '2017-02-07', 'transaction_type' => 1, 'transaction_id' => 21, 'customer_id' => 7, 'due_date' => '2017-02-15', 'total' => 900, 'status' => '1' ] );
        DB::table('sales')->insert( [ 'date' => '2017-02-09', 'transaction_type' => 1, 'transaction_id' => 22, 'customer_id' => 7, 'due_date' => '2017-02-19', 'total' => 600, 'status' => '1' ] );

        DB::table('sales')->insert( [ 'date' => '2017-02-11', 'transaction_type' => 1, 'transaction_id' => 23, 'customer_id' => 8, 'due_date' => '2017-02-15', 'total' => 200, 'status' => '1' ] );
        DB::table('sales')->insert( [ 'date' => '2017-02-12', 'transaction_type' => 1, 'transaction_id' => 24, 'customer_id' => 8, 'due_date' => '2017-02-20', 'total' => 300, 'status' => '1' ] );
        DB::table('sales')->insert( [ 'date' => '2017-02-12', 'transaction_type' => 1, 'transaction_id' => 25, 'customer_id' => 8, 'due_date' => '2017-02-23', 'total' => 1300, 'status' => '1' ] );

        DB::table('sales')->insert( [ 'date' => '2017-02-13', 'transaction_type' => 1, 'transaction_id' => 26, 'customer_id' => 9, 'due_date' => '2017-02-28', 'total' => 800, 'status' => '1' ] );
        DB::table('sales')->insert( [ 'date' => '2017-02-14', 'transaction_type' => 1, 'transaction_id' => 27, 'customer_id' => 9, 'due_date' => '2017-03-02', 'total' => 600, 'status' => '1' ] );
        DB::table('sales')->insert( [ 'date' => '2017-02-15', 'transaction_type' => 1, 'transaction_id' => 28, 'customer_id' => 9, 'due_date' => '2017-03-13', 'total' => 1700, 'status' => '1' ] );
    }
}
