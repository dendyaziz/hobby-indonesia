<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'parent_category_id',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if (empty($category->slug) && !empty($category->name)) {
                $category->slug = \Illuminate\Support\Str::slug($category->name);
            }
        });

        static::deleting(function (Category $category) {
            if ($category->subCategories()->exists()) {
                \Filament\Notifications\Notification::make()
                    ->warning()
                    ->title('Cannot delete category')
                    ->body("Category \"{$category->name}\" has subcategories and cannot be deleted.")
                    ->send();

                return false;
            }
        });
    }

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

    /**
     * Get the products associated with this category.
     *
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product', 'category_id', 'product_id')->withTimestamps();
    }
}
