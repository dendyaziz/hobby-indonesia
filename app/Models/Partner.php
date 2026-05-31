<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Partner extends Model
{
    /** @use HasFactory<\Database\Factories\PartnerFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'image',
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
}
