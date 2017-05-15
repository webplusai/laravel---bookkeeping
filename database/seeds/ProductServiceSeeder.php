<?php

use Illuminate\Database\Seeder;

class ProductServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('product_service')->truncate();

        DB::table('product_service')->insert( [ 'name' => 'Product1', 'sku' => 'sku111111', 'price' => '100', 'product_category_id' => 1 ] );
        DB::table('product_service')->insert( [ 'name' => 'Product2', 'sku' => 'sku111111', 'price' => '100', 'product_category_id' => 2 ] );
        DB::table('product_service')->insert( [ 'name' => 'Product3', 'sku' => 'sku222222', 'price' => '100', 'product_category_id' => 3 ] );
        DB::table('product_service')->insert( [ 'name' => 'Product4', 'sku' => 'sku222222', 'price' => '100', 'product_category_id' => 4 ] );
        DB::table('product_service')->insert( [ 'name' => 'Product5', 'sku' => 'sku333333', 'price' => '100', 'product_category_id' => 5 ] );
    }
}
