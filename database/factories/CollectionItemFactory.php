<?php

namespace Database\Factories;

use App\Models\CollectionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionItem>
 */
use App\Models\Collection;
use App\Models\Product;

class CollectionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'collection_id' => Collection::factory(),
            'product_id' => fn () => Product::create([
                'name' => $this->faker->words(3, true),
                'slug' => \Illuminate\Support\Str::slug($this->faker->words(3, true)),
                'availability' => 'Available',
                'price' => 100000,
                'description' => '<p>Description</p>',
            ])->id,
        ];
    }
}
