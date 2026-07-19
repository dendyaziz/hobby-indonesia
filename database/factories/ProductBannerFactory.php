<?php

namespace Database\Factories;

use App\Models\ProductBanner;

class ProductBannerFactory extends BannerFactory
{
    protected $model = ProductBanner::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return array_merge(parent::definition(), [
            'placement' => 'product-page--top',
        ]);
    }
}
