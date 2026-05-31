<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Partner extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\PartnerFactory> */
    use HasFactory, HasUuids;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (Partner $partner) {
            $partner->position = (static::max('position') ?? 0) + 1;
        });
    }

    /**
     * The events that incorporate this partner.
     *
     * @return BelongsToMany<Event, $this>
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_partner', 'partner_id', 'event_id')->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('partners')
            ->registerMediaConversions(function (Media $media): void {
                $this
                    ->addMediaConversion('small')
                    ->height(40)
                    ->format('webp');

                $this
                    ->addMediaConversion('thumbnail')
                    ->height(150)
                    ->format('webp');
            });
    }
}
