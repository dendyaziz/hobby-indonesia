<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\HasTags;

class Product extends Model implements HasMedia
{
    use HasTags;
    use HasUuids;
    use InteractsWithMedia;

    protected static function booted(): void
    {
        static::saved(function (): void {
            Cache::tags(['public'])->flush();
        });

        static::deleted(function (): void {
            Cache::tags(['public'])->flush();
        });
    }

    protected $fillable = [
        'name',
        'slug',
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
        'tiktok_videos',
        'description',
        'difficulty',
        'themes',
    ];

    protected function casts(): array
    {
        return [
            'tiktok_videos' => 'array',
            'themes' => 'array',
        ];
    }

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
