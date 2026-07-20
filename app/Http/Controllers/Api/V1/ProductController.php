<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Display a listing of products with dynamic filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = min(max($request->integer('limit', 8), 1), 100);
        $page = max($request->integer('page', 1), 1);

        $filters = $request->only([
            'categories',
            'min_age',
            'playing_time_from',
            'playing_time_to',
            'total_player_from',
            'total_player_to',
            'sort',
        ]);

        // Normalize filters to ensure stable cache keys
        ksort($filters);
        $hash = md5(json_encode($filters));
        $key = "product__pagination_limit_{$limit}_page_{$page}_hash_{$hash}";

        $data = Cache::tags(['public'])->remember($key, now()->addDays(30), function () use ($limit, $page, $filters): array {
            $query = Product::query()->with('media');

            // Category filter
            if (! empty($filters['categories'])) {
                $categories = $filters['categories'];
                if (is_string($categories)) {
                    $categories = array_filter(explode(',', $categories));
                }
                if (! empty($categories)) {
                    $query->whereHas('categories', function ($q) use ($categories) {
                        $q->whereIn('slug', $categories);
                    });
                }
            }

            // Minimum age filter (suitable for age or above, i.e., min_age >= value)
            if (isset($filters['min_age'])) {
                $query->where('min_age', '>=', (int) $filters['min_age']);
            }

            // Playing duration filters
            if (isset($filters['playing_time_from'])) {
                $query->where('playing_duration', '>=', (int) $filters['playing_time_from']);
            }
            if (isset($filters['playing_time_to'])) {
                $query->where('playing_duration', '<=', (int) $filters['playing_time_to']);
            }

            // Total player count filters
            if (isset($filters['total_player_from'])) {
                $query->where('max_player', '>=', (int) $filters['total_player_from']);
            }
            if (isset($filters['total_player_to'])) {
                $query->where('min_player', '<=', (int) $filters['total_player_to']);
            }

            // Sort logic
            $sort = $filters['sort'] ?? 'featured';
            if ($sort === 'priceAsc') {
                $query->orderByRaw('COALESCE(discounted_price, price) ASC');
            } elseif ($sort === 'priceDesc') {
                $query->orderByRaw('COALESCE(discounted_price, price) DESC');
            } elseif ($sort === 'nameAsc') {
                $query->orderBy('name', 'asc');
            } else {
                $query->orderByDesc('created_at');
            }

            $products = $query->paginate($limit, ['*'], 'page', $page);

            return [
                'data' => ProductResource::collection($products->getCollection())->resolve(),
                'links' => [
                    'first' => $products->url(1),
                    'last' => $products->url($products->lastPage()),
                    'prev' => $products->previousPageUrl(),
                    'next' => $products->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'from' => $products->firstItem(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'to' => $products->lastItem(),
                    'total' => $products->total(),
                ],
            ];
        });

        return response()->json($data);
    }
}
