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
        DB::table('users')->insert(
            [
                'name'       => 'Aisah Kanaya',
                'email'      => 'qlab@code.com',
                'password'   => Hash::make('a'),
                'nomor'      => '085',
                "status"     => 1,
                "role"       => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->insert(
            [
                'name'       => 'BINA INSTAN TAQWA',
                'email'      => 'bina@mail.com',
                'password'   => Hash::make('rahasia'),
                'nomor'      => '085',
                "status"     => 1,
                "role"       => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
