<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EmployeePosition;
use App\Enums\EmployeeStatus;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'full_name' => fake()->name(),
            'registration_number' => 'EMP-'.fake()->unique()->numerify('####'),
            'position' => fake()->randomElement(EmployeePosition::cases()),
            'status' => EmployeeStatus::Active,
            'join_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'salary' => fake()->randomFloat(2, 40000, 150000),
            'daily_rate' => fake()->randomFloat(2, 5000, 20000),
            'address' => fake()->city(),
            'profile_photo_path' => null,
            'photo_hash' => null,
        ];
    }

    /**
     * Indicate that the employee is linked to a user.
     */
    public function forUser(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }
}
