<?php

use App\Models\SocialMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('it returns 404 if no social media exists', function () {
    $this->getJson('/api/v1/social-media')
        ->assertNotFound();
});

test('it returns the first social media model as a resource if it exists', function () {
    $socialMedia = SocialMedia::factory()->create([
        'facebook' => 'https://facebook.com/hobbyindonesia',
        'instagram' => 'https://instagram.com/hobbyindonesia',
        'youtube' => 'https://youtube.com/hobbyindonesia',
        'x' => 'https://x.com/hobbyindonesia',
    ]);

    $this->getJson('/api/v1/social-media')
        ->assertOk()
        ->assertJson([
            'data' => [
                'facebook' => 'https://facebook.com/hobbyindonesia',
                'instagram' => 'https://instagram.com/hobbyindonesia',
                'youtube' => 'https://youtube.com/hobbyindonesia',
                'x' => 'https://x.com/hobbyindonesia',
            ],
        ])
        ->assertJsonMissing([
            'id',
            'created_at',
            'updated_at',
        ]);
});

test('it caches the social media data for 30 days', function () {
    expect(Cache::tags(['public'])->has('social-media'))->toBeFalse();

    $socialMedia = SocialMedia::factory()->create();

    // Call endpoint to populate cache
    $this->getJson('/api/v1/social-media')->assertOk();

    // Verify cache has it
    expect(Cache::tags(['public'])->has('social-media'))->toBeTrue();
    expect(Cache::tags(['public'])->get('social-media')->id)->toBe($socialMedia->id);
});

test('it clears the cache when the social media model is updated', function () {
    $socialMedia = SocialMedia::factory()->create();

    // Call endpoint to populate cache
    $this->getJson('/api/v1/social-media')->assertOk();
    expect(Cache::tags(['public'])->has('social-media'))->toBeTrue();

    // Update the social media model
    $socialMedia->update([
        'facebook' => 'https://facebook.com/updated',
    ]);

    // Verify cache is cleared
    expect(Cache::tags(['public'])->has('social-media'))->toBeFalse();
});

test('it clears the cache when the social media model is deleted', function () {
    $socialMedia = SocialMedia::factory()->create();

    // Call endpoint to populate cache
    $this->getJson('/api/v1/social-media')->assertOk();
    expect(Cache::tags(['public'])->has('social-media'))->toBeTrue();

    // Delete the social media model
    $socialMedia->delete();

    // Verify cache is cleared
    expect(Cache::tags(['public'])->has('social-media'))->toBeFalse();
});

test('it can clear all public caches by tag', function () {
    $socialMedia = SocialMedia::factory()->create();

    // Call endpoint to populate cache
    $this->getJson('/api/v1/social-media')->assertOk();
    expect(Cache::tags(['public'])->has('social-media'))->toBeTrue();

    // Flush the tag
    Cache::tags(['public'])->flush();

    // Verify cache is cleared
    expect(Cache::tags(['public'])->has('social-media'))->toBeFalse();
});
