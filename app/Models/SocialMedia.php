<?php

namespace App\Models;

use Database\Factories\SocialMediaFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SocialMedia extends Model
{
    /** @use HasFactory<SocialMediaFactory> */
    use HasFactory, HasUuids;

    protected $table = 'social_medias';

    protected $fillable = [
        'facebook',
        'instagram',
        'tiktok',
        'youtube',
        'x',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saved(function (): void {
            Cache::tags(['public'])->forget('social-media');
        });

        static::deleted(function (): void {
            Cache::tags(['public'])->forget('social-media');
        });
    }
}
