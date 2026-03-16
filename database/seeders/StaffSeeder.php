<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $staffAccounts = [
            // 管理部（admin_user）
            [
                'name'     => 'Admin HRI',
                'email'    => 'admin@hri-check.com',
                'password' => Hash::make('HRIAdmin2026!'),
                'role_type' => 'admin_user',
            ],
            // 調査部（investigator_user）× 2名
            [
                'name'     => 'Investigator 1',
                'email'    => 'investigator1@hri-check.com',
                'password' => Hash::make('HRIInvestigator2026!'),
                'role_type' => 'investigator_user',
            ],
            [
                'name'     => 'Investigator 2',
                'email'    => 'investigator2@hri-check.com',
                'password' => Hash::make('HRIInvestigator2026!'),
                'role_type' => 'investigator_user',
            ],
            // 審査部（reviewer_user）× 2名
            [
                'name'     => 'Reviewer 1',
                'email'    => 'reviewer1@hri-check.com',
                'password' => Hash::make('HRIReviewer2026!'),
                'role_type' => 'reviewer_user',
            ],
            [
                'name'     => 'Reviewer 2',
                'email'    => 'reviewer2@hri-check.com',
                'password' => Hash::make('HRIReviewer2026!'),
                'role_type' => 'reviewer_user',
            ],
        ];

        foreach ($staffAccounts as $staff) {
            // 同じメールがすでに存在する場合はスキップ（二重登録防止）
            User::firstOrCreate(
                ['email' => $staff['email']],
                [
                    'name'              => $staff['name'],
                    'password'          => $staff['password'],
                    'role_type'         => $staff['role_type'],
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}