<?php

use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('supplier')->truncate();

        DB::table('supplier')->insert( [ 'name' => 'Supplier1', 'company' => 'Company1', 'email' => 'email1@email.com', 'country' => 'Country1', 'city' => 'City1', 'phone' => '111-1111-1111', 'address1' => 'Address11', 'address2' => 'Address21', 'note' => 'Note1', 'is_active' => 1 ] );
        DB::table('supplier')->insert( [ 'name' => 'Supplier2', 'company' => 'Company2', 'email' => 'email2@email.com', 'country' => 'Country2', 'city' => 'City2', 'phone' => '222-2222-2222', 'address1' => 'Address12', 'address2' => 'Address22', 'note' => 'Note2', 'is_active' => 1 ] );
        DB::table('supplier')->insert( [ 'name' => 'Supplier3', 'company' => 'Company3', 'email' => 'email3@email.com', 'country' => 'Country3', 'city' => 'City3', 'phone' => '333-3333-3333', 'address1' => 'Address13', 'address2' => 'Address23', 'note' => 'Note3', 'is_active' => 1 ] );
    }
}
