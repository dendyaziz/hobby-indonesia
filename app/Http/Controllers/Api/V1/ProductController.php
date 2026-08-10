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
            'themes',
            'difficulty',
            'min_age',
            'playing_time_from',
            'playing_time_to',
            'total_player_from',
            'total_player_to',
            'price_from',
            'price_to',
            'sort',
            'search',
            'exclude_slug',
        ]);

        // Normalize filters to ensure stable cache keys
        ksort($filters);
        $hash = md5(json_encode($filters));
        $key = "product__pagination_limit_{$limit}_page_{$page}_hash_{$hash}";

        $data = Cache::tags(['public'])->remember($key, now()->addDays(30), function () use ($limit, $page, $filters): array {
            $query = Product::query()->with('media');

            // Search filter (name LIKE %term%)
            if (! empty($filters['search'])) {
                $term = $filters['search'];
                $query->where('name', 'LIKE', "%{$term}%");
            }

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

            // Themes filter
            if (! empty($filters['themes'])) {
                $themes = $filters['themes'];
                if (is_string($themes)) {
                    $themes = array_filter(explode(',', $themes));
                }
                if (! empty($themes)) {
                    $query->where(function ($q) use ($themes) {
                        foreach ($themes as $theme) {
                            $q->orWhereJsonContains('themes', $theme);
                        }
                    });
                }
            }

            // Difficulty filter
            if (! empty($filters['difficulty'])) {
                $difficulty = $filters['difficulty'];
                if (is_string($difficulty)) {
                    $difficulty = array_filter(explode(',', $difficulty));
                }
                if (! empty($difficulty)) {
                    $query->whereIn('difficulty', $difficulty);
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

            // Price range filters (checks effective price: discounted_price if set, otherwise price)
            if (isset($filters['price_from'])) {
                $query->whereRaw('COALESCE(discounted_price, price) >= ?', [(int) $filters['price_from']]);
            }
            if (isset($filters['price_to'])) {
                $query->whereRaw('COALESCE(discounted_price, price) <= ?', [(int) $filters['price_to']]);
            }

            // Exclude slug filter
            if (! empty($filters['exclude_slug'])) {
                $excludeSlugs = is_array($filters['exclude_slug'])
                    ? $filters['exclude_slug']
                    : array_filter(explode(',', (string) $filters['exclude_slug']));

                if (! empty($excludeSlugs)) {
                    $query->whereNotIn('slug', $excludeSlugs);
                }
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

    /**
     * Display the specified product by its slug.
     */
    public function show(string $slug): JsonResponse
    {
        $data = Cache::tags(['public'])->remember("product__detail_{$slug}", now()->addDays(30), function () use ($slug) {
            $product = Product::where('slug', $slug)
                ->with([
                    'media',
                    'categories',
                    'characters' => fn ($query) => $query->orderBy('position'),
                    'guides' => fn ($query) => $query->orderBy('position'),
                    'boxItems' => fn ($query) => $query->orderBy('position'),
                ])
                ->firstOrFail();

            return (new ProductResource($product))->resolve();
        });

        return response()->json([
            'data' => $data,
        ]);
    }
}
