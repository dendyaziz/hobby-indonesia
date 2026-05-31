<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Event extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory, HasUuids;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
    ];

    /**
     * The partners that are incorporated in this event.
     *
     * @return BelongsToMany<Partner, $this>
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'event_partner', 'event_id', 'partner_id')->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('events')
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
