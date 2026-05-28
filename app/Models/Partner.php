<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
