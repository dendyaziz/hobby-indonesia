<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
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
            'content' => '<p>' . implode('</p><p>', $this->faker->paragraphs(3)) . '</p>',
            'status' => $this->faker->randomElement(['draft', 'published']),
        ];
    }
}
