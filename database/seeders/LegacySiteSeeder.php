<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacySiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sites = [
            [
                'id' => 1,
                'customer_id' => 15,
                'site_code' => 'GTIM-001',
                'full_name' => 'GROUPEMENT-TIMIMOUN',
                'short_name' => 'GTIM',
                'location' => 'Hassi Barouda – Adrar',
                'map_link' => 'https://maps.app.goo.gl/TzDZeVucgXdqKfy7A',
                'created_at' => '2026-03-16 14:24:15',
                'updated_at' => '2026-06-18 21:58:41',
            ],
            [
                'id' => 3,
                'customer_id' => 1,
                'site_code' => 'GTG-003',
                'full_name' => null,
                'short_name' => 'GTG',
                'location' => 'Gassitouil',
                'map_link' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'id' => 4,
                'customer_id' => 16,
                'site_code' => 'HTJ-004',
                'full_name' => 'SH -  H.T.J',
                'short_name' => 'HTJ',
                'location' => 'Timimoun',
                'map_link' => null,
                'created_at' => null,
                'updated_at' => '2026-05-13 07:36:52',
            ],
            [
                'id' => 5,
                'customer_id' => 16,
                'site_code' => 'TNK-005',
                'full_name' => 'SH - T.N.K',
                'short_name' => 'TNK',
                'location' => 'Timimoun',
                'map_link' => null,
                'created_at' => null,
                'updated_at' => '2026-05-13 07:37:11',
            ],
            [
                'id' => 6,
                'customer_id' => 16,
                'site_code' => 'HBH-006',
                'full_name' => 'SH - H.B.H',
                'short_name' => 'HBH',
                'location' => 'Timimoun',
                'map_link' => null,
                'created_at' => null,
                'updated_at' => '2026-05-13 07:37:41',
            ],
            [
                'id' => 13,
                'customer_id' => 18,
                'site_code' => 'STAH-013',
                'full_name' => "CPF d'ALRAR - STAH",
                'short_name' => 'STAH',
                'location' => 'In-Amenas',
                'map_link' => 'https://maps.app.goo.gl/qaQmiBPFvZsyZ6J99',
                'created_at' => '2026-04-06 07:01:15',
                'updated_at' => '2026-06-18 12:35:12',
            ],
            [
                'id' => 14,
                'customer_id' => 17,
                'site_code' => 'GTFT',
                'full_name' => 'Groupement - TFT',
                'short_name' => 'G-TFT',
                'location' => 'Tin Fouyé Tabenkort',
                'map_link' => null,
                'created_at' => '2026-05-12 06:51:00',
                'updated_at' => '2026-07-01 09:36:11',
            ],
        ];

        foreach ($sites as $data) {
            // Ensure referenced customer exists or set to null
            if (! empty($data['customer_id']) && ! Customer::where('id', $data['customer_id'])->exists()) {
                $data['customer_id'] = null;
            }

            DB::table('sites')->updateOrInsert(
                ['id' => $data['id']],
                $data
            );
        }
    }
}
