<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'image',
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
}
