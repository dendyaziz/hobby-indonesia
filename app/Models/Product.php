<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasUuids;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'availability',
        'price',
        'discount_percentage',
        'discounted_price',
        'brand',
        'manufacture_country',
        'publisher',
        'designer',
        'artist',
        'min_age',
        'min_player',
        'max_player',
        'playing_duration',
        'youtube',
        'description',
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('product-images')
            ->registerMediaConversions(function (Media $media): void {
                $this
                    ->addMediaConversion('small')
                    ->height(40)
                    ->format('webp');

                $this
                    ->addMediaConversion('thumbnail')
                    ->height(400)
                    ->format('webp');
            });
    }

    /**
     * Get the categories associated with this product.
     *
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id')->withTimestamps();
    }
}
