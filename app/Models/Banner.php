<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    /** @use HasFactory<\Database\Factories\BannerFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'image',
        'placement',
        'type',
        'url',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Banner $banner) {
            $banner->position = (static::where('placement', $banner->placement)->max('position') ?? 0) + 1;
        });
    }
}
