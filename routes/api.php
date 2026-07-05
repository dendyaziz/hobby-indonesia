<?php

use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\SocialMediaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('contact', [ContactController::class, 'show']);
    Route::get('social-media', [SocialMediaController::class, 'show']);
});
