<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $siteName = fake()->city().' Operational Station';

        return [
            'customer_id' => Customer::factory(),
            'site_code' => 'SIT-'.fake()->unique()->numerify('####'),
            'full_name' => $siteName,
            'short_name' => mb_substr(preg_replace('/[^A-Za-z0-9]/', '', $siteName), 0, 10),
            'location' => fake()->city().' – '.fake()->country(),
            'map_link' => 'https://maps.app.goo.gl/'.fake()->lexify('?????????????????'),
        ];
    }
}
