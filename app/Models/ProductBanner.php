<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ProductBanner extends Banner
{
    protected $table = 'banners';

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('placement', function (Builder $builder): void {
            $builder->where('placement', 'product-page--top');
        });

        static::creating(function (ProductBanner $banner): void {
            $banner->placement = 'product-page--top';
        });

        static::saved(function (): void {
            $today = now()->toDateString();
            Cache::tags(['public'])->forget("product-banner__list_{$today}");
        });

        static::deleted(function (): void {
            $today = now()->toDateString();
            Cache::tags(['public'])->forget("product-banner__list_{$today}");
        });
    }
}
