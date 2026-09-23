<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyWarrantySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warranties = [
            [
                'id' => 2,
                'reference' => 'G-SPE-004',
                'bank_name' => 'BNA',
                'amount' => 200000.00,
                'started_at' => '2024-12-02',
                'status' => 'released',
                'type' => 'bid_bond',
                'created_at' => '2026-04-26 13:56:40',
                'updated_at' => '2026-08-02 06:25:42',
            ],
            [
                'id' => 3,
                'reference' => 'G-SPE-003',
                'bank_name' => 'BEA',
                'amount' => 150000.00,
                'started_at' => '2024-08-03',
                'status' => 'released',
                'type' => 'bid_bond',
                'created_at' => '2026-04-26 14:19:15',
                'updated_at' => '2026-08-02 06:25:17',
            ],
            [
                'id' => 5,
                'reference' => 'G-ENSP-002',
                'bank_name' => 'BNA',
                'amount' => 300000.00,
                'started_at' => '2024-02-28',
                'status' => 'released',
                'type' => 'bid_bond',
                'created_at' => '2026-05-11 11:23:07',
                'updated_at' => '2026-08-02 06:24:24',
            ],
            [
                'id' => 6,
                'reference' => 'G-SPE-005',
                'bank_name' => 'BEA',
                'amount' => 150000.00,
                'started_at' => '2026-02-25',
                'status' => 'released',
                'type' => 'bid_bond',
                'created_at' => '2026-08-02 06:27:15',
                'updated_at' => '2026-08-02 06:30:38',
            ],
            [
                'id' => 7,
                'reference' => 'G-SPE-006',
                'bank_name' => 'BEA',
                'amount' => 60000.00,
                'started_at' => '2026-02-25',
                'status' => 'released',
                'type' => 'bid_bond',
                'created_at' => '2026-08-02 06:29:14',
                'updated_at' => '2026-08-02 06:30:35',
            ],
            [
                'id' => 8,
                'reference' => 'G-SPE-007',
                'bank_name' => 'BEA',
                'amount' => 50000.00,
                'started_at' => '2026-02-25',
                'status' => 'released',
                'type' => 'bid_bond',
                'created_at' => '2026-08-02 06:30:00',
                'updated_at' => '2026-08-02 06:30:32',
            ],
            [
                'id' => 9,
                'reference' => 'G-STAH-006',
                'bank_name' => 'BNA',
                'amount' => 2500000.00,
                'started_at' => '2025-05-20',
                'status' => 'released',
                'type' => 'bid_bond',
                'created_at' => '2026-08-02 06:31:42',
                'updated_at' => '2026-08-02 06:32:45',
            ],
            [
                'id' => 10,
                'reference' => 'G-GTL-008',
                'bank_name' => 'BEA',
                'amount' => 23269238.00,
                'started_at' => '2020-11-03',
                'status' => 'released',
                'type' => 'performance',
                'created_at' => '2026-08-02 06:36:02',
                'updated_at' => '2026-08-02 06:36:02',
            ],
            [
                'id' => 11,
                'reference' => 'G-GTFT-009',
                'bank_name' => 'BEA',
                'amount' => 1204711.20,
                'started_at' => '2022-11-12',
                'status' => 'active',
                'type' => 'performance',
                'created_at' => '2026-08-02 06:37:24',
                'updated_at' => '2026-08-02 06:37:24',
            ],
            [
                'id' => 12,
                'reference' => 'G-SH/ADRAR-010',
                'bank_name' => 'BEA',
                'amount' => 4651600.00,
                'started_at' => '2024-07-24',
                'status' => 'active',
                'type' => 'performance',
                'created_at' => '2026-08-02 06:39:01',
                'updated_at' => '2026-08-02 06:39:01',
            ],
            [
                'id' => 13,
                'reference' => 'G-STAH-011',
                'bank_name' => 'BEA',
                'amount' => 1592640.87,
                'started_at' => '2026-02-26',
                'status' => 'active',
                'type' => 'performance',
                'created_at' => '2026-08-02 06:40:14',
                'updated_at' => '2026-08-02 06:40:14',
            ],
        ];

        foreach ($warranties as $data) {
            DB::table('garanties')->updateOrInsert(
                ['id' => $data['id']],
                $data
            );
        }
    }
}
