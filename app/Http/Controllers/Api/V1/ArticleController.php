<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleListResource;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    /**
     * Display a listing of published articles without their content.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = min(max($request->integer('limit', $request->integer('per_page', 10)), 1), 100);
        $page = max($request->integer('page', 1), 1);
        $excludeSlug = $request->query('exclude_slug');

        $excludeHash = $excludeSlug ? md5(is_array($excludeSlug) ? implode(',', $excludeSlug) : (string) $excludeSlug) : 'none';
        $key = "article__pagination_limit_{$limit}_page_{$page}_exclude_{$excludeHash}";

        $data = Cache::tags(['public', 'article'])->remember($key, now()->addDays(30), function () use ($limit, $page, $excludeSlug): array {
            $query = Article::query()
                ->with('media')
                ->where('status', 'published');

            if (! empty($excludeSlug)) {
                $excludeSlugs = is_array($excludeSlug)
                    ? $excludeSlug
                    : array_filter(explode(',', (string) $excludeSlug));

                if (! empty($excludeSlugs)) {
                    $query->whereNotIn('slug', $excludeSlugs);
                }
            }

            $articles = $query->orderByDesc('created_at')
                ->paginate($limit, ['*'], 'page', $page);

            return [
                'data' => ArticleListResource::collection($articles->getCollection())->resolve(),
                'links' => [
                    'first' => $articles->url(1),
                    'last' => $articles->url($articles->lastPage()),
                    'prev' => $articles->previousPageUrl(),
                    'next' => $articles->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $articles->currentPage(),
                    'from' => $articles->firstItem(),
                    'last_page' => $articles->lastPage(),
                    'per_page' => $articles->perPage(),
                    'to' => $articles->lastItem(),
                    'total' => $articles->total(),
                ],
            ];
        });

        return response()->json($data);
    }

    /**
     * Display a published article by slug.
     */
    public function show(Article $article): JsonResponse
    {
        abort_unless($article->status === 'published', 404);

        $key = "article__{$article->slug}";
        $data = Cache::tags(['public', 'article'])->remember($key, now()->addDays(30), function () use ($article): array {
            $article->loadMissing('media');

            return ArticleResource::make($article)->resolve();
        });

        return response()->json(['data' => $data]);
    }
}
