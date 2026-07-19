<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductBannerResource;
use App\Models\ProductBanner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ProductBannerController extends Controller
{
    /**
     * Display a listing of active and not-expired product banners.
     */
    public function index(): JsonResponse
    {
        $today = now()->toDateString();

        $data = Cache::tags(['public'])->remember("product-banner__list_{$today}", now()->addDay(), function () use ($today): array {
            $productBanners = ProductBanner::query()
                ->with('media')
                ->where('status', 'active')
                ->where(function (Builder $query) use ($today): void {
                    $query->whereNull('start_date')
                        ->orWhere('start_date', '<=', $today);
                })
                ->where(function (Builder $query) use ($today): void {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', $today);
                })
                ->orderBy('position')
                ->get();

            return ProductBannerResource::collection($productBanners)->resolve();
        });

        return response()->json(['data' => $data]);
    }
}
