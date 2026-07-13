<?php

use App\Models\ResellerBanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('it returns active and not-expired reseller banners', function () {
    $banner = ResellerBanner::factory()->create([
        'title' => 'Reseller Deal',
        'placement' => 'reseller-page--top',
        'status' => 'active',
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
    ]);

    $this->getJson('/api/v1/reseller-banners')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Reseller Deal')
        ->assertJsonMissing(['id', 'created_at', 'updated_at', 'placement']);
});

test('it caches reseller banner list data and flushes cache when model changes', function () {
    $banner = ResellerBanner::factory()->create([
        'title' => 'Reseller Deal',
        'placement' => 'reseller-page--top',
        'status' => 'active',
    ]);

    $today = now()->toDateString();
    $key = "reseller-banner__list_{$today}";
    expect(Cache::tags(['public'])->has($key))->toBeFalse();

    $this->getJson('/api/v1/reseller-banners')->assertOk();
    expect(Cache::tags(['public'])->has($key))->toBeTrue();

    $banner->update(['title' => 'Updated Reseller Deal']);
    expect(Cache::tags(['public'])->has($key))->toBeFalse();
});
