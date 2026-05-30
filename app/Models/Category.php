<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'parent_category_id',
    ];

    /**
     * Get the parent category.
     *
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_category_id');
    }

    /**
     * Get the subcategories.
     *
     * @return HasMany<Category, $this>
     */
    public function subCategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_category_id');
    }

    /**
     * Scope a query to only include parent categories.
     */
    public function scopeParent(Builder $query): Builder
    {
        return $query->whereNull('parent_category_id');
    }

    /**
     * Scope a query to only include sub categories.
     */
    public function scopeSub(Builder $query): Builder
    {
        return $query->whereNotNull('parent_category_id');
    }
}
