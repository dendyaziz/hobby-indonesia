<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResellerBannerResource;
use App\Models\ResellerBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ResellerBannerController extends Controller
{
    /**
     * Display a listing of active and not-expired reseller banners.
     */
    public function index(): JsonResponse
    {
        $today = now()->toDateString();

        $data = Cache::tags(['public'])->remember("reseller-banner__list_{$today}", now()->addDay(), function () use ($today) {
            $banners = ResellerBanner::query()
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

            return ResellerBannerResource::collection($banners)->resolve();
        });

        return response()->json(['data' => $data]);
    }
}
