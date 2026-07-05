<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SocialMediaResource;
use App\Models\SocialMedia;
use Illuminate\Support\Facades\Cache;

class SocialMediaController extends Controller
{
    /**
     * Display the company social media.
     */
    public function show(): SocialMediaResource
    {
        $socialMedia = Cache::tags(['public'])->remember('social-media', now()->addDays(30), function () {
            return SocialMedia::first();
        });

        if (! $socialMedia) {
            abort(404);
        }

        return SocialMediaResource::make($socialMedia);
    }
}
