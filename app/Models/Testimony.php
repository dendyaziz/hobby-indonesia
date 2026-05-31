<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Testimony extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\TestimonyFactory> */
    use HasFactory, HasUuids;
    use InteractsWithMedia;

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
