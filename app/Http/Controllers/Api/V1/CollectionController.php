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
            $collection->loadMissing(['items.product.media', 'items.product.tags', 'items.product.categories']);

            $products = $collection->items
                ->map(fn ($item) => $item->product)
                ->filter();

            return ProductResource::collection($products)->resolve();
        });

        $categories = $request->query('categories');
        if (! empty($categories)) {
            $categorySlugs = is_array($categories)
                ? $categories
                : array_filter(explode(',', (string) $categories));

            if (! empty($categorySlugs)) {
                $data = array_values(array_filter($data, function (array $product) use ($categorySlugs): bool {
                    $prodCategories = $product['categories'] ?? [];
                    $prodCatSlugs = array_map(function ($cat) {
                        return is_array($cat) ? ($cat['slug'] ?? null) : ($cat->slug ?? null);
                    }, $prodCategories);

                    return ! empty(array_intersect($prodCatSlugs, $categorySlugs));
                }));
            }
        }

        $excludeSlug = $request->query('exclude_slug');
        if (! empty($excludeSlug)) {
            $excludeSlugs = is_array($excludeSlug)
                ? $excludeSlug
                : array_filter(explode(',', (string) $excludeSlug));

            if (! empty($excludeSlugs)) {
                $data = array_values(array_filter($data, function ($product) use ($excludeSlugs): bool {
                    $prodSlug = is_array($product) ? ($product['slug'] ?? null) : ($product->slug ?? null);

                    return ! in_array($prodSlug, $excludeSlugs, true);
                }));
            }
        }

        return response()->json(['data' => $data]);
    }
}
