<?php

namespace App\Models;

use Database\Factories\TestimonyFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Testimony extends Model implements HasMedia
{
    /** @use HasFactory<TestimonyFactory> */
    use HasFactory, HasUuids;

    use InteractsWithMedia;

    protected static function booted(): void
    {
        static::saved(function (): void {
            Cache::tags(['public'])->flush();
        });

        static::deleted(function (): void {
            Cache::tags(['public'])->flush();
        });
    }

    protected $fillable = [
        'name',
        'subtitle',
        'testimony',
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('testimonies')
            ->registerMediaConversions(function (Media $media): void {
                $this
                    ->addMediaConversion('small')
                    ->height(40)
                    ->format('webp');
            });
    }
}
