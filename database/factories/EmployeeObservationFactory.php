<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeObservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeObservation>
 */
class EmployeeObservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'author_id' => User::factory(),
            'observed_on' => fake()->dateTimeBetween('-2 months')->format('Y-m-d'),
            'nature' => fake()->randomElement(array_keys(EmployeeObservation::NATURES)),
            'category' => fake()->randomElement(array_keys(EmployeeObservation::CATEGORIES)),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraph(),
            'visible_to_employee' => false,
        ];
    }

    public function visibleToEmployee(): static
    {
        return $this->state(fn (array $attributes) => [
            'visible_to_employee' => true,
        ]);
    }
}
