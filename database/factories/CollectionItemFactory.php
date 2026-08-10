<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\CollectionItem;
/**
 * @extends Factory<CollectionItem>
 */
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
                'slug' => Str::slug($this->faker->words(3, true)),
                'availability' => 'Available',
                'price' => 100000,
                'description' => '<p>Description</p>',
            ])->id,
        ];
    }
}
