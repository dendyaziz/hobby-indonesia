<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class HeroBanner extends Banner
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
            $builder->where('placement', 'homepage--hero');
        });

        static::creating(function (HeroBanner $banner): void {
            $banner->placement = 'homepage--hero';
        });

        static::saved(function (): void {
            $today = now()->toDateString();
            Cache::tags(['public'])->forget("hero-banner__list_{$today}");
        });

        static::deleted(function (): void {
            $today = now()->toDateString();
            Cache::tags(['public'])->forget("hero-banner__list_{$today}");
        });
    }
}
