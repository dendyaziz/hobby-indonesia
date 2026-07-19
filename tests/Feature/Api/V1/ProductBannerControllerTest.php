<?php

use App\Models\HeroBanner;
use App\Models\ProductBanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['cache.default' => 'array']);
    app('cache')->purge();
    Cache::store('array')->flush();
});

test('it returns active product banners and hides internal attributes', function () {
    $second = ProductBanner::factory()->create([
        'title' => 'Second Product Banner',
        'status' => 'active',
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
    ]);
    $first = ProductBanner::factory()->create([
        'title' => 'First Product Banner',
        'status' => 'active',
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
    ]);
    $first->position = 1;
    $first->save();
    $second->position = 2;
    $second->save();

    HeroBanner::factory()->create(['title' => 'Hero Banner']);
    ProductBanner::factory()->create(['status' => 'inactive']);
    ProductBanner::factory()->create(['start_date' => now()->addDay()]);
    ProductBanner::factory()->create(['end_date' => now()->subDay()]);

    $this->getJson('/api/v1/product-banners')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.title', 'First Product Banner')
        ->assertJsonPath('data.1.title', 'Second Product Banner')
        ->assertJsonMissing([
            'id',
            'created_at',
            'updated_at',
            'placement',
            'start_date',
            'end_date',
        ]);
});

test('it caches and invalidates the product banners list', function () {
    $key = 'product-banner__list_'.now()->toDateString();
    $banner = ProductBanner::factory()->create();

    $this->getJson('/api/v1/product-banners')->assertOk();
    expect(Cache::tags(['public'])->has($key))->toBeTrue();

    $banner->update(['title' => 'Updated Product Banner']);

    expect(Cache::tags(['public'])->has($key))->toBeFalse();
});
