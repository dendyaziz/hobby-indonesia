<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PartnerController extends Controller
{
    /**
     * Display a listing of the active partners.
     */
    public function index(): JsonResponse
    {
        $data = Cache::tags(['public'])->remember('partner__list', now()->addDays(30), function () {
            $partners = Partner::query()
                ->with('media')
                ->where('status', 'active')
                ->orderBy('position', 'asc')
                ->get();

            return PartnerResource::collection($partners)->resolve();
        });

        return response()->json(['data' => $data]);
    }
}
