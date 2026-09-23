<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = fake()->company();

        return [
            'reference' => 'CLI-'.fake()->unique()->numerify('####'),
            'company_name' => $company,
            'short_name' => mb_substr(preg_replace('/[^A-Za-z0-9]/', '', $company), 0, 10),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'website' => 'https://www.'.fake()->domainName(),
            'registration_number' => 'RC-'.fake()->numerify('##########'),
            'notes' => fake()->sentence(),
        ];
    }
}
