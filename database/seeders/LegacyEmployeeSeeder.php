<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user1 = User::find(1);

        $employees = [
            [
                'id' => 1,
                'user_id' => $user1?->id,
                'full_name' => 'MAYATA Nadhir',
                'registration_number' => 'Nadhir',
                'position' => 'metering_engineer',
                'status' => 'active',
                'join_date' => '2026-03-05',
                'salary' => 75000.00,
                'daily_rate' => 8000.00,
                'address' => 'Alger',
                'profile_photo_path' => $user1?->profile_photo_path,
                'photo_hash' => $user1?->photo_hash,
                'created_at' => '2026-03-16 14:21:01',
                'updated_at' => '2026-05-11 12:23:53',
            ],
            [
                'id' => 2,
                'user_id' => null,
                'full_name' => 'MAYATA Raouf',
                'registration_number' => 'Raouf',
                'position' => 'senior_metering_engineer',
                'status' => 'active',
                'join_date' => '2026-03-05',
                'salary' => 80000.00,
                'daily_rate' => 10000.00,
                'address' => 'Alger',
                'profile_photo_path' => null,
                'photo_hash' => null,
                'created_at' => '2026-03-16 14:21:18',
                'updated_at' => '2026-06-01 07:12:18',
            ],
            [
                'id' => 3,
                'user_id' => null,
                'full_name' => 'ASMA Younes',
                'registration_number' => 'Younes',
                'position' => 'senior_metering_engineer',
                'status' => 'active',
                'join_date' => '2026-03-16',
                'salary' => 140000.00,
                'daily_rate' => 10000.00,
                'address' => 'Alger',
                'profile_photo_path' => null,
                'photo_hash' => null,
                'created_at' => '2026-03-16 14:21:38',
                'updated_at' => '2026-06-01 07:12:43',
            ],
            [
                'id' => 4,
                'user_id' => null,
                'full_name' => 'MAYATA Ahmed',
                'registration_number' => 'Ahmed',
                'position' => 'general_manager',
                'status' => 'active',
                'join_date' => '2026-03-16',
                'salary' => 0.00,
                'daily_rate' => 0.00,
                'address' => 'Alger',
                'profile_photo_path' => null,
                'photo_hash' => null,
                'created_at' => '2026-03-16 14:21:56',
                'updated_at' => '2026-05-11 12:22:36',
            ],
            [
                'id' => 5,
                'user_id' => null,
                'full_name' => 'BOUGUERN Mourad',
                'registration_number' => 'Mourad',
                'position' => 'senior_instrumentation_engineer',
                'status' => 'active',
                'join_date' => '2026-03-16',
                'salary' => 0.00,
                'daily_rate' => 30000.00,
                'address' => 'Skikda',
                'profile_photo_path' => null,
                'photo_hash' => null,
                'created_at' => '2026-03-16 14:22:09',
                'updated_at' => '2026-06-01 07:11:10',
            ],
            [
                'id' => 25,
                'user_id' => null,
                'full_name' => 'GUEBLI Imed-Eddine',
                'registration_number' => 'Imed',
                'position' => 'metering_engineer',
                'status' => 'active',
                'join_date' => '2026-05-11',
                'salary' => 0.00,
                'daily_rate' => 8000.00,
                'address' => 'Alger',
                'profile_photo_path' => null,
                'photo_hash' => null,
                'created_at' => '2026-05-11 12:26:14',
                'updated_at' => '2026-05-12 05:39:25',
            ],
            [
                'id' => 26,
                'user_id' => null,
                'full_name' => 'Amir CHIRIF',
                'registration_number' => 'Amir',
                'position' => 'instrumentation_technician',
                'status' => 'active',
                'join_date' => '2026-07-24',
                'salary' => 0.00,
                'daily_rate' => 0.00,
                'address' => 'Alger',
                'profile_photo_path' => null,
                'photo_hash' => null,
                'created_at' => '2026-07-24 16:53:07',
                'updated_at' => '2026-07-24 16:53:07',
            ],
        ];

        // Clean up any incorrectly mapped auto-increment records (e.g. 6, 7)
        DB::table('employees')->whereIn('registration_number', ['Imed', 'Amir'])->whereNotIn('id', [25, 26])->delete();

        foreach ($employees as $data) {
            DB::table('employees')->updateOrInsert(
                ['id' => $data['id']],
                $data
            );
        }
    }
}
