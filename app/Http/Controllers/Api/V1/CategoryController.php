<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(): JsonResponse
    {
        $data = Cache::tags(['public'])->remember('category__list', now()->addDays(30), function () {
            $categories = Category::query()
                ->withCount('products')
                ->orderBy('name', 'asc')
                ->get();

            return CategoryResource::collection($categories)->resolve();
        });

        return response()->json(['data' => $data]);
    }
}
