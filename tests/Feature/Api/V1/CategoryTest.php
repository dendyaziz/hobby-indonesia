<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('it returns categories list', function () {
    Category::factory()->create(['name' => 'Strategy']);
    Category::factory()->create(['name' => 'Family']);

    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'Family')
        ->assertJsonPath('data.1.name', 'Strategy');
});

test('it caches category list data and flushes cache when category changes', function () {
    $category = Category::factory()->create(['name' => 'Strategy']);

    $key = 'category__list';
    expect(Cache::tags(['public'])->has($key))->toBeFalse();

    $this->getJson('/api/v1/categories')->assertOk();
    expect(Cache::tags(['public'])->has($key))->toBeTrue();

    $category->update(['name' => 'Updated Strategy']);
    expect(Cache::tags(['public'])->has($key))->toBeFalse();
});
