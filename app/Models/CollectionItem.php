<?php

namespace App\Models;

use Database\Factories\CollectionItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class CollectionItem extends Model
{
    /** @use HasFactory<CollectionItemFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'collection_id',
        'product_id',
        'position',
    ];

    protected static function booted(): void
    {
        static::creating(function (CollectionItem $item) {
            $item->position = (static::where('collection_id', $item->collection_id)->max('position') ?? 0) + 1;
        });

        static::saved(function (CollectionItem $item): void {
            static::forgetCollectionProductCache($item->collection_id);
            static::forgetCollectionProductCache($item->getOriginal('collection_id'));
        });

        static::deleted(function (CollectionItem $item): void {
            static::forgetCollectionProductCache($item->collection_id);
        });
    }

    private static function forgetCollectionProductCache(?string $collectionId): void
    {
        if (! $collectionId) {
            return;
        }

        $slug = Collection::query()->whereKey($collectionId)->value('slug');

        if ($slug) {
            Cache::tags(['public'])->forget("collection__products_{$slug}");
        }
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
