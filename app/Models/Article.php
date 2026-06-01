<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Article extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ArticleFactory> */
    use HasFactory, HasUuids;
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'content',
        'status',
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('featured_images')
            ->registerMediaConversions(function (Media $media): void {
                $this
                    ->addMediaConversion('small')
                    ->height(40)
                    ->format('webp');

                $this
                    ->addMediaConversion('thumbnail')
                    ->height(400)
                    ->format('webp');
            });
    }
}
