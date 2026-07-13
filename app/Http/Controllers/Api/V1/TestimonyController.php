<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TestimonyResource;
use App\Models\Testimony;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class TestimonyController extends Controller
{
    /**
     * Display a listing of the testimonies.
     */
    public function index(): JsonResponse
    {
        $data = Cache::tags(['public'])->remember('testimony__list', now()->addDays(30), function () {
            $testimonies = Testimony::query()
                ->with('media')
                ->latest()
                ->get();

            return TestimonyResource::collection($testimonies)->resolve();
        });

        return response()->json(['data' => $data]);
    }
}
