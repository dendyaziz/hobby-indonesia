<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => substr($this->faker->sentence(), 0, 100),
            'placement' => 'homepage--hero',
            'type' => 'none',
            'url' => null,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays(7),
        ];
    }
}
