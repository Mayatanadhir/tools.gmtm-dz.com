<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Grandeur;
use Illuminate\Database\Seeder;

class GrandeurSeeder extends Seeder
{
    public function run(): void
    {
        $grandeurs = [
            ['id' => 1, 'name' => 'Pression', 'symbol' => 'Bar', 'type' => 'measurement'],
            ['id' => 2, 'name' => 'Pression', 'symbol' => 'Bar', 'type' => 'source'],
            ['id' => 3, 'name' => 'Température', 'symbol' => '°C', 'type' => 'measurement'],
            ['id' => 4, 'name' => 'Température', 'symbol' => '°C', 'type' => 'source'],
            ['id' => 5, 'name' => 'Courant', 'symbol' => 'mA', 'type' => 'measurement'],
            ['id' => 6, 'name' => 'Courant', 'symbol' => 'mA', 'type' => 'source'],
            ['id' => 7, 'name' => 'Tension', 'symbol' => 'V', 'type' => 'measurement'],
            ['id' => 8, 'name' => 'Tension', 'symbol' => 'V', 'type' => 'source'],
            ['id' => 9, 'name' => 'Résistance', 'symbol' => 'Ω', 'type' => 'measurement'],
            ['id' => 10, 'name' => 'Résistance', 'symbol' => 'Ω', 'type' => 'source'],
            ['id' => 12, 'name' => 'Frequence', 'symbol' => 'Hz', 'type' => 'source'],
            ['id' => 13, 'name' => 'Impultion', 'symbol' => 'imp', 'type' => 'source'],
            ['id' => 15, 'name' => 'Pression', 'symbol' => 'mBar', 'type' => 'measurement'],
        ];

        foreach ($grandeurs as $g) {
            Grandeur::updateOrCreate(['id' => $g['id']], $g);
        }
    }
}
