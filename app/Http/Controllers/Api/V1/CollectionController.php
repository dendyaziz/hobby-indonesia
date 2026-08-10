<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CollectionController extends Controller
{
    /**
     * Display the products inside a collection.
     */
    public function products(Request $request, Collection $collection): JsonResponse
    {
        $slug = $collection->slug;

        $data = Cache::tags(['public'])->remember("collection__products_{$slug}", now()->addDays(30), function () use ($collection) {
            $collection->loadMissing(['items.product.media', 'items.product.tags']);

            $products = $collection->items
                ->map(fn ($item) => $item->product)
                ->filter();

            return ProductResource::collection($products)->resolve();
        });

        $excludeSlug = $request->query('exclude_slug');
        if (! empty($excludeSlug)) {
            $excludeSlugs = is_array($excludeSlug)
                ? $excludeSlug
                : array_filter(explode(',', (string) $excludeSlug));

            if (! empty($excludeSlugs)) {
                $data = array_values(array_filter($data, function (array $product) use ($excludeSlugs): bool {
                    return ! in_array($product['slug'] ?? null, $excludeSlugs, true);
                }));
            }
        }

        return response()->json(['data' => $data]);
    }
}
