<?php

use App\Models\Testimony;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('it returns testimonies list', function () {
    Testimony::factory()->create([
        'name' => 'Alice',
        'subtitle' => 'Customer',
        'testimony' => 'Great experience',
    ]);

    $this->getJson('/api/v1/testimonies')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Alice')
        ->assertJsonMissing(['id', 'created_at', 'updated_at']);
});

test('it caches testimony list data and flushes cache when testimony changes', function () {
    $testimony = Testimony::factory()->create([
        'name' => 'Alice',
    ]);

    $key = 'testimony__list';
    expect(Cache::tags(['public'])->has($key))->toBeFalse();

    $this->getJson('/api/v1/testimonies')->assertOk();
    expect(Cache::tags(['public'])->has($key))->toBeTrue();

    $testimony->update(['name' => 'Updated Alice']);
    expect(Cache::tags(['public'])->has($key))->toBeFalse();
});
