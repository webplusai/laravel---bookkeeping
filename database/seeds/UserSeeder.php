<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('user')->truncate();

        DB::table('user')->insert( [ 'name' => 'First user', 'email' => 'first@email.com', 'password' => Hash::make('firstuser')] );
        DB::table('user')->insert( [ 'name' => 'Second user', 'email' => 'second@email.com', 'password' => Hash::make('seconduser')] );
        DB::table('user')->insert( [ 'name' => 'Third user', 'email' => 'third@email.com', 'password' => Hash::make('thirduser')] );
    }
}
