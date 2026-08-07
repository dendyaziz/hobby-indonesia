<?php

use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('it returns empty list if no faqs exist', function () {
    $this->getJson('/api/v1/faqs')
        ->assertOk()
        ->assertJson([
            'data' => [],
        ]);
});

test('it returns faqs list with correct resource representation', function () {
    $faq = Faq::create([
        'question' => 'How to play?',
        'answer' => 'Follow the guides.',
    ]);

    $this->getJson('/api/v1/faqs')
        ->assertOk()
        ->assertJson([
            'data' => [
                [
                    'question' => 'How to play?',
                    'answer' => 'Follow the guides.',
                ],
            ],
        ])
        ->assertJsonMissing([
            'id',
            'created_at',
            'updated_at',
        ]);
});

test('it caches the faq data for 30 days', function () {
    expect(Cache::tags(['public'])->has('faq__list'))->toBeFalse();

    Faq::create([
        'question' => 'Cached question?',
        'answer' => 'Cached answer.',
    ]);

    // Call endpoint to populate cache
    $this->getJson('/api/v1/faqs')->assertOk();

    // Verify cache has it
    expect(Cache::tags(['public'])->has('faq__list'))->toBeTrue();
});

test('it clears the cache when a faq model is saved', function () {
    $faq = Faq::create([
        'question' => 'Q?',
        'answer' => 'A.',
    ]);

    // Call endpoint to populate cache
    $this->getJson('/api/v1/faqs')->assertOk();
    expect(Cache::tags(['public'])->has('faq__list'))->toBeTrue();

    // Update
    $faq->update([
        'answer' => 'Updated A.',
    ]);

    // Verify cache is cleared
    expect(Cache::tags(['public'])->has('faq__list'))->toBeFalse();
});

test('it clears the cache when a faq model is deleted', function () {
    $faq = Faq::create([
        'question' => 'Q?',
        'answer' => 'A.',
    ]);

    // Call endpoint to populate cache
    $this->getJson('/api/v1/faqs')->assertOk();
    expect(Cache::tags(['public'])->has('faq__list'))->toBeTrue();

    // Delete
    $faq->delete();

    // Verify cache is cleared
    expect(Cache::tags(['public'])->has('faq__list'))->toBeFalse();
});
