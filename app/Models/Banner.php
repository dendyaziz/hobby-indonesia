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
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Banner $banner) {
            $banner->position = (static::where('placement', $banner->placement)->max('position') ?? 0) + 1;
        });
    }
}
