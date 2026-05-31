<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->realText(50),
            'description' => '<p><strong>' . $this->faker->sentence() . '</strong></p><p>' . $this->faker->paragraph(2) . '</p>',
        ];
    }
}
