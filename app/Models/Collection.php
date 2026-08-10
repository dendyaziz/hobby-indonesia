<?php

namespace App\Models;

use Database\Factories\CollectionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    /** @use HasFactory<CollectionFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'position',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (Collection $collection) {
            $collection->position = (static::max('position') ?? 0) + 1;
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(CollectionItem::class)->orderBy('position');
    }
}
