<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'id' => 15,
                'reference' => 'GTIM',
                'company_name' => 'GROUPEMENT - TIMIMOUN',
                'short_name' => 'GTIM',
                'address' => null,
                'phone' => null,
                'email' => null,
                'website' => null,
                'registration_number' => null,
                'notes' => null,
                'created_at' => '2026-05-12 19:18:59',
                'updated_at' => '2026-06-01 07:14:19',
            ],
            [
                'id' => 16,
                'reference' => 'SH-DP-ADR',
                'company_name' => 'SONATRACH - DP - ADR',
                'short_name' => 'SH-DP-ADR',
                'address' => null,
                'phone' => null,
                'email' => null,
                'website' => null,
                'registration_number' => null,
                'notes' => null,
                'created_at' => '2026-05-12 20:01:05',
                'updated_at' => '2026-06-01 07:14:37',
            ],
            [
                'id' => 17,
                'reference' => 'GTFT',
                'company_name' => 'GROUPEMENT - TFT',
                'short_name' => 'GTFT',
                'address' => null,
                'phone' => null,
                'email' => null,
                'website' => null,
                'registration_number' => null,
                'notes' => null,
                'created_at' => '2026-05-12 20:04:01',
                'updated_at' => '2026-06-01 07:14:03',
            ],
            [
                'id' => 18,
                'reference' => 'SH/DP/STAH',
                'company_name' => "SONATRACH - CPF d'ALRAR - STAH",
                'short_name' => 'SH/DP/STAH',
                'address' => null,
                'phone' => null,
                'email' => null,
                'website' => null,
                'registration_number' => null,
                'notes' => null,
                'created_at' => '2026-06-18 11:04:38',
                'updated_at' => '2026-06-18 12:22:50',
            ],
        ];

        foreach ($customers as $data) {
            DB::table('customers')->updateOrInsert(
                ['id' => $data['id']],
                $data
            );
        }
    }
}
