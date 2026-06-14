<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionItem extends Model
{
    /** @use HasFactory<\Database\Factories\CollectionItemFactory> */
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
