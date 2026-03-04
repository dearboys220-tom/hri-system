<?php

namespace Database\Seeders;

use App\Enums\RoleType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'admin',
                'email' => 'admin@hri.test',
                'password' => Hash::make('password123'),
                'role_type' => RoleType::ADMIN,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'reviewer',
                'email' => 'reviewer@hri.test',
                'password' => Hash::make('password123'),
                'role_type' => RoleType::REVIEWER,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'investigator',
                'email' => 'investigator@hri.test',
                'password' => Hash::make('password123'),
                'role_type' => RoleType::INVESTIGATOR,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
