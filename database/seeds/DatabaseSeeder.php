<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // $this->call(UserSeeder::class);
        // $this->call(SalesSeeder::class);
        // $this->call(ExpenseSeeder::class);
        $this->call(AccountSeeder::class);
        // $this->call(CustomerSeeder::class);
        // $this->call(SupplierSeeder::class);
        // $this->call(ProductServiceSeeder::class);
        // $this->call(ProductCategorySeeder::class);
        $this->call(AccountDetailTypeSeeder::class);
        $this->call(AccountCategoryTypeSeeder::class);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
