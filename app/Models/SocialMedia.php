<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    use HasUuids;

    protected $table = 'social_medias';

    protected $fillable = [
        'facebook',
        'instagram',
        'youtube',
        'x',
    ];
}
