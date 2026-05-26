<?php

namespace Database\Factories;

use App\Models\Testimony;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimony>
 */
class TestimonyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => substr(fake()->name(), 0, 30),
            'subtitle' => substr(fake()->company(), 0, 50),
            'testimony' => fake()->paragraph(),
            'image' => null,
        ];
    }
}
