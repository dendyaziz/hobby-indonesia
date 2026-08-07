<?php

namespace App\Models;

use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Faq extends Model
{
    /** @use HasFactory<FaqFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'question',
        'answer',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            Cache::tags(['public'])->flush();
        });

        static::deleted(function (): void {
            Cache::tags(['public'])->flush();
        });
    }
}
