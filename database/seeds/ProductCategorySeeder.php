<?php

use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('product_category')->truncate();

        DB::table('product_category')->insert( [ 'name' => 'Category1' ] );
        DB::table('product_category')->insert( [ 'name' => 'Category2' ] );
        DB::table('product_category')->insert( [ 'name' => 'Category3' ] );
        DB::table('product_category')->insert( [ 'name' => 'Category4' ] );
        DB::table('product_category')->insert( [ 'name' => 'Category5' ] );
    }
}
