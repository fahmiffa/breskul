<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User
        DB::table('users')->insert([
            'name'       => 'Test User',
            'email'      => 'test@test.com',
            'password'   => Hash::make('rahasia'),
            'nomor'      => '085',
            "status"     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
