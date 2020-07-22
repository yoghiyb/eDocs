<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'role' => 1,
            'username' => 'yoghiyb',
            'email' => 'yoghiyb@gmail.com',
            'password' => Hash::make('12345678'),
            'api_token' => Str::random(60),
        ]);

        $faker = Faker::create('id_ID');

        for ($i = 1; $i <= 9; $i++) {
            DB::table('users')->insert([
                'role' => rand(2, 3),
                'username' => $faker->name,
                'email' => $faker->email,
                'password' => Hash::make('12345678'),
                'api_token' => Str::random(60),
                'dept_id' => rand(1, 2)
            ]);
        }
    }
}
