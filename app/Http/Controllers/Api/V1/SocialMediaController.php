<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SocialMediaResource;
use App\Models\SocialMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SocialMediaController extends Controller
{
    /**
     * Display the company social media.
     */
    public function show(): JsonResponse
    {
        $data = Cache::tags(['public'])->remember('social-media', now()->addDays(30), function () {
            $socialMedia = SocialMedia::first();

            if (! $socialMedia) {
                return null;
            }

            return SocialMediaResource::make($socialMedia)->resolve();
        });

        if (! $data) {
            abort(404);
        }

        return response()->json(['data' => $data]);
    }
}
