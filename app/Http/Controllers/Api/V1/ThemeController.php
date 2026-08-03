<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ThemeController extends Controller
{
    public const THEMES = [
        'Abstract',
        'Adventure',
        'Animals',
        'City Building',
        'Civilization',
        'Cooperative',
        'Deduction / Mystery',
        'Dungeon Crawler',
        'Economy',
        'Fantasy',
        'Horror',
        'Racing',
        'Sci-Fi',
        'Survival',
        'War / Historical',
    ];

    /**
     * Display a listing of all themes with product counts.
     */
    public function index(): JsonResponse
    {
        $data = Cache::tags(['public'])->remember('theme__list', now()->addDays(30), function (): array {
            $themeData = [];
            foreach (self::THEMES as $theme) {
                $count = Product::whereJsonContains('themes', $theme)->count();
                $themeData[] = [
                    'name' => $theme,
                    'products_count' => $count,
                ];
            }

            return $themeData;
        });

        return response()->json(['data' => $data]);
    }
}
