<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class FaqController extends Controller
{
    /**
     * Display a listing of the FAQs.
     */
    public function index(): JsonResponse
    {
        $data = Cache::tags(['public'])->remember('faq__list', now()->addDays(30), function () {
            $faqs = Faq::query()
                ->latest()
                ->get();

            return FaqResource::collection($faqs)->resolve();
        });

        return response()->json(['data' => $data]);
    }
}
