<?php

namespace Database\Factories;

use App\Models\AttendanceLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceLocation>
 */
class AttendanceLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Agence '.fake()->unique()->city(),
            'latitude' => fake()->latitude(5.25, 5.45),
            'longitude' => fake()->longitude(-4.10, -3.90),
            'radius_meters' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
