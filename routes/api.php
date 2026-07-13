<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CollectionController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\HeroBannerController;
use App\Http\Controllers\Api\V1\PartnerController;
use App\Http\Controllers\Api\V1\ResellerBannerController;
use App\Http\Controllers\Api\V1\SocialMediaController;
use App\Http\Controllers\Api\V1\TestimonyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('contact', [ContactController::class, 'show']);
    Route::get('hero-banners', [HeroBannerController::class, 'index']);
    Route::get('social-media', [SocialMediaController::class, 'show']);
    Route::get('collections/{collection:slug}/products', [CollectionController::class, 'products']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('reseller-banners', [ResellerBannerController::class, 'index']);
    Route::get('testimonies', [TestimonyController::class, 'index']);
    Route::get('partners', [PartnerController::class, 'index']);
});
