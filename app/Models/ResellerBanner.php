<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class ResellerBanner extends Banner
{
    use HasFactory;

    protected $table = 'banners';

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('placement', function (Builder $builder): void {
            $builder->where('placement', 'reseller-page--top');
        });

        static::creating(function (ResellerBanner $banner): void {
            $banner->placement = 'reseller-page--top';
        });

        static::saved(function (): void {
            $today = now()->toDateString();
            Cache::tags(['public'])->forget("reseller-banner__list_{$today}");
        });

        static::deleted(function (): void {
            $today = now()->toDateString();
            Cache::tags(['public'])->forget("reseller-banner__list_{$today}");
        });
    }
}
