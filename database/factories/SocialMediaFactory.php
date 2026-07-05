<?php

namespace Database\Factories;

use App\Models\SocialMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialMedia>
 */
class SocialMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'facebook' => 'https://facebook.com/'.$this->faker->userName(),
            'instagram' => 'https://instagram.com/'.$this->faker->userName(),
            'youtube' => 'https://youtube.com/'.$this->faker->userName(),
            'x' => 'https://x.com/'.$this->faker->userName(),
        ];
    }
}
