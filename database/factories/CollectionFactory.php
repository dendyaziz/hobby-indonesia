<?php

namespace Database\Factories;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends Factory<Collection>
 */
use Illuminate\Support\Str;

class CollectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->words(3, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
