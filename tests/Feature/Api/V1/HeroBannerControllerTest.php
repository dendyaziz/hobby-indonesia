<?php

use App\Models\HeroBanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

test('it returns an empty collection if no hero banners exist', function () {
    $this->getJson('/api/v1/hero-banners')
        ->assertOk()
        ->assertJson([
            'data' => [],
        ]);
});

test('it returns active and not-expired hero banners, hiding internal attributes', function () {
    // 1. Create a valid active Hero Banner
    $hero = HeroBanner::factory()->create([
        'title' => 'Main Hero',
        'placement' => 'homepage--hero',
        'type' => 'none',
        'status' => 'active',
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
    ]);

    // Attach fake media to the banner
    $hero->addMedia(UploadedFile::fake()->image('hero.jpg'))
        ->toMediaCollection('banners');

    $this->getJson('/api/v1/hero-banners')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Main Hero')
        ->assertJsonMissing([
            'id',
            'created_at',
            'updated_at',
            'start_date',
            'end_date',
        ]);
});

test('it filters out inactive, future scheduled, or past expired hero banners', function () {
    // Active and valid (should be returned)
    $active = HeroBanner::factory()->create([
        'title' => 'Active Banner',
        'status' => 'active',
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
    ]);

    // Inactive (should be filtered)
    HeroBanner::factory()->create([
        'title' => 'Inactive Banner',
        'status' => 'inactive',
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
    ]);

    // Scheduled for future (should be filtered)
    HeroBanner::factory()->create([
        'title' => 'Future Banner',
        'status' => 'active',
        'start_date' => now()->addDays(2),
        'end_date' => now()->addDays(5),
    ]);

    // Expired in past (should be filtered)
    HeroBanner::factory()->create([
        'title' => 'Expired Banner',
        'status' => 'active',
        'start_date' => now()->subDays(5),
        'end_date' => now()->subDay(),
    ]);

    $this->getJson('/api/v1/hero-banners')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Active Banner');
});

test('it orders the hero banners by position ASC', function () {
    // Let's create multiple hero banners with different positions to test position sorting.
    $banner2 = HeroBanner::factory()->create([
        'title' => 'Banner 2',
        'status' => 'active',
        'start_date' => null,
        'end_date' => null,
    ]);
    // The position is auto-assigned as 1 for the first homepage--hero banner.

    $banner1 = HeroBanner::factory()->create([
        'title' => 'Banner 1',
        'status' => 'active',
        'start_date' => null,
        'end_date' => null,
    ]);
    // The position is auto-assigned as 2. Let's manually swap positions to test sequence ordering.
    $banner1->position = 1;
    $banner1->save();

    $banner2->position = 2;
    $banner2->save();

    $this->getJson('/api/v1/hero-banners')
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Banner 1')
        ->assertJsonPath('data.1.title', 'Banner 2');
});

test('it caches the hero banners list data', function () {
    $key = 'hero-banner__list_'.now()->toDateString();
    expect(Cache::tags(['public'])->has($key))->toBeFalse();

    HeroBanner::factory()->create([
        'status' => 'active',
        'start_date' => null,
        'end_date' => null,
    ]);

    // Call endpoint to populate cache
    $this->getJson('/api/v1/hero-banners')->assertOk();

    // Verify cache has it
    expect(Cache::tags(['public'])->has($key))->toBeTrue();
});

test('it clears the cache when a hero banner is updated', function () {
    $key = 'hero-banner__list_'.now()->toDateString();
    $banner = HeroBanner::factory()->create([
        'status' => 'active',
        'start_date' => null,
        'end_date' => null,
    ]);

    $this->getJson('/api/v1/hero-banners')->assertOk();
    expect(Cache::tags(['public'])->has($key))->toBeTrue();

    // Update the banner
    $banner->update([
        'title' => 'Updated Banner Title',
    ]);

    expect(Cache::tags(['public'])->has($key))->toBeFalse();
});

test('it clears the cache when a hero banner is deleted', function () {
    $key = 'hero-banner__list_'.now()->toDateString();
    $banner = HeroBanner::factory()->create([
        'status' => 'active',
        'start_date' => null,
        'end_date' => null,
    ]);

    $this->getJson('/api/v1/hero-banners')->assertOk();
    expect(Cache::tags(['public'])->has($key))->toBeTrue();

    // Delete the banner
    $banner->delete();

    expect(Cache::tags(['public'])->has($key))->toBeFalse();
});
