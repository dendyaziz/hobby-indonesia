<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'availability',
        'price',
        'discount_percentage',
        'discounted_price',
        'brand',
        'manufacture_country',
        'publisher',
        'designer',
        'artist',
        'min_age',
        'min_player',
        'max_player',
        'playing_duration',
        'youtube',
        'description',
    ];
}
