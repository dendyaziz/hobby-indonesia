<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\HeroBannerResource;
use App\Models\HeroBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HeroBannerController extends Controller
{
    /**
     * Display a listing of active and not-expired hero banners.
     */
    public function index(): JsonResponse
    {
        $today = now()->toDateString();

        $data = Cache::tags(['public'])->remember("hero-banner__list_{$today}", now()->addDay(), function () use ($today) {
            $heroBanners = HeroBanner::query()
                ->with('media')
                ->where('status', 'active')
                ->where(function ($query) use ($today) {
                    $query->whereNull('start_date')
                        ->orWhere('start_date', '<=', $today);
                })
                ->where(function ($query) use ($today) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', $today);
                })
                ->orderBy('position', 'asc')
                ->get();

            return HeroBannerResource::collection($heroBanners)->resolve();
        });

        return response()->json(['data' => $data]);
    }
}
