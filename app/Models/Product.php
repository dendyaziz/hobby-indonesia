<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'playbook_url',
        'tiktok_videos',
        'description',
        'everything_you_need_to_know_description',
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

        $this
            ->addMediaCollection('everything-you-need-to-know-image')
            ->singleFile()
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

    /**
     * Get the characters associated with this product.
     *
     * @return HasMany<ProductCharacter, $this>
     */
    public function characters(): HasMany
    {
        return $this->hasMany(ProductCharacter::class)->orderBy('position');
    }

    /**
     * Get the guides associated with this product.
     *
     * @return HasMany<ProductGuide, $this>
     */
    public function guides(): HasMany
    {
        return $this->hasMany(ProductGuide::class)->orderBy('position');
    }

    /**
     * Get the box items associated with this product.
     *
     * @return HasMany<ProductBoxItem, $this>
     */
    public function boxItems(): HasMany
    {
        return $this->hasMany(ProductBoxItem::class)->orderBy('position');
    }
}
