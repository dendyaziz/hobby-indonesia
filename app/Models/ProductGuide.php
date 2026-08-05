<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductGuide extends Model implements HasMedia
{
    use HasUuids;
    use InteractsWithMedia;

    protected $fillable = [
        'product_id',
        'title',
        'description',
        'position',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProductGuide $guide): void {
            if (! $guide->position) {
                $guide->position = (static::where('product_id', $guide->product_id)->max('position') ?? 0) + 1;
            }
        });

        static::saved(function (): void {
            Cache::tags(['public'])->flush();
        });

        static::deleted(function (): void {
            Cache::tags(['public'])->flush();
        });
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('guide-image')
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
     * Get the product that owns the guide.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
