<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ContactController extends Controller
{
    /**
     * Display the app contact.
     */
    public function show(): JsonResponse
    {
        $data = Cache::tags(['public'])->remember('contact', now()->addDays(30), function () {
            $contact = Contact::first();

            if (! $contact) {
                return null;
            }

            return ContactResource::make($contact)->resolve();
        });

        if (! $data) {
            abort(404);
        }

        return response()->json(['data' => $data]);
    }
}
