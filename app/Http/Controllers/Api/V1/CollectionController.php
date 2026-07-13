<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CollectionController extends Controller
{
    /**
     * Display the products inside a collection.
     */
    public function products(Collection $collection): JsonResponse
    {
        $slug = $collection->slug;

        $data = Cache::tags(['public'])->remember("collection__products_{$slug}", now()->addDays(30), function () use ($collection) {
            $collection->loadMissing(['items.product.media', 'items.product.tags']);

            $products = $collection->items
                ->map(fn ($item) => $item->product)
                ->filter();

            return ProductResource::collection($products)->resolve();
        });

        return response()->json(['data' => $data]);
    }
}
