<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimony extends Model
{
    /** @use HasFactory<\Database\Factories\TestimonyFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'testimony',
        'image',
    ];
}
