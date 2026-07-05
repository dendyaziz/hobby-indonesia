<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Support\Facades\Cache;

class ContactController extends Controller
{
    /**
     * Display the app contact.
     */
    public function show(): ContactResource
    {
        $contact = Cache::tags(['public_app'])->remember('app_contact', now()->addDays(30), function () {
            return Contact::first();
        });

        if (! $contact) {
            abort(404);
        }

        return ContactResource::make($contact);
    }
}
