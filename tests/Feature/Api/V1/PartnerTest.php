<?php

use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('it returns partners list', function () {
    Partner::factory()->create([
        'name' => 'Publisher A',
        'status' => 'active',
    ]);
    Partner::factory()->create([
        'name' => 'Publisher B',
        'status' => 'inactive',
    ]);

    $this->getJson('/api/v1/partners')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Publisher A')
        ->assertJsonMissing(['id', 'created_at', 'updated_at']);
});

test('it caches partner list data and flushes cache when partner changes', function () {
    $partner = Partner::factory()->create([
        'name' => 'Publisher A',
        'status' => 'active',
    ]);

    $key = 'partner__list';
    expect(Cache::tags(['public'])->has($key))->toBeFalse();

    $this->getJson('/api/v1/partners')->assertOk();
    expect(Cache::tags(['public'])->has($key))->toBeTrue();

    $partner->update(['name' => 'Updated Publisher A']);
    expect(Cache::tags(['public'])->has($key))->toBeFalse();
});
