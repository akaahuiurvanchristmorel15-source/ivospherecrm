<?php

namespace Database\Factories;

use App\Models\CyberService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CyberService>
 */
class CyberServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'category' => fake()->randomElement(array_keys(CyberService::CATEGORIES)),
            'unit' => 'page',
            'price' => fake()->randomElement([50, 100, 200, 500, 1000]),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
