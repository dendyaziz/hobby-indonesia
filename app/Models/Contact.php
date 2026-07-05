<?php

namespace App\Models;

use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory, HasUuids;

    protected $table = 'contacts';

    protected $fillable = [
        'company_name',
        'telephone',
        'email',
        'address',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saved(function (): void {
            Cache::tags(['public_app'])->forget('app_contact');
        });

        static::deleted(function (): void {
            Cache::tags(['public_app'])->forget('app_contact');
        });
    }
}
