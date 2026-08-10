<?php

use App\Models\Category;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('it returns products inside a collection', function () {
    $collection = Collection::factory()->create(['slug' => 'best-seller', 'title' => 'Best Seller']);

    $product1 = Product::create([
        'name' => 'Game 1',
        'slug' => 'game-1',
        'availability' => 'Available',
        'price' => 10000,
        'description' => 'Description 1',
    ]);

    $product2 = Product::create([
        'name' => 'Game 2',
        'slug' => 'game-2',
        'availability' => 'Available',
        'price' => 20000,
        'description' => 'Description 2',
    ]);

    CollectionItem::create([
        'collection_id' => $collection->id,
        'product_id' => $product1->id,
        'position' => 1,
    ]);

    CollectionItem::create([
        'collection_id' => $collection->id,
        'product_id' => $product2->id,
        'position' => 2,
    ]);

    $this->getJson('/api/v1/collections/best-seller/products')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'Game 1')
        ->assertJsonPath('data.1.name', 'Game 2');
});

test('it returns all product images inside a collection', function () {
    Storage::fake('public');

    $collection = Collection::factory()->create(['slug' => 'best-seller', 'title' => 'Best Seller']);
    $product = Product::create([
        'name' => 'Game 1',
        'slug' => 'game-1',
        'availability' => 'Available',
        'price' => 10000,
        'description' => 'Description 1',
    ]);

    $product->addMedia(UploadedFile::fake()->image('game-1.jpg'))->toMediaCollection('product-images');
    $product->addMedia(UploadedFile::fake()->image('game-1-detail.jpg'))->toMediaCollection('product-images');

    CollectionItem::create([
        'collection_id' => $collection->id,
        'product_id' => $product->id,
        'position' => 1,
    ]);

    $this->getJson('/api/v1/collections/best-seller/products')
        ->assertOk()
        ->assertJsonCount(2, 'data.0.image_urls')
        ->assertJsonMissingPath('data.0.image_url');
});

test('it caches products list data and flushes cache when product changes', function () {
    $collection = Collection::factory()->create(['slug' => 'best-seller', 'title' => 'Best Seller']);
    $product = Product::create([
        'name' => 'Game 1',
        'slug' => 'game-1',
        'availability' => 'Available',
        'price' => 10000,
        'description' => 'Description 1',
    ]);
    CollectionItem::create([
        'collection_id' => $collection->id,
        'product_id' => $product->id,
        'position' => 1,
    ]);

    $key = 'collection__products_best-seller';
    expect(Cache::tags(['public'])->has($key))->toBeFalse();

    $this->getJson('/api/v1/collections/best-seller/products')->assertOk();
    expect(Cache::tags(['public'])->has($key))->toBeTrue();

    // Updating product flushes public cache tags
    $product->update(['name' => 'Updated Game']);
    expect(Cache::tags(['public'])->has($key))->toBeFalse();
});

test('it flushes the collection products cache when collection membership changes', function () {
    $collection = Collection::factory()->create(['slug' => 'best-seller', 'title' => 'Best Seller']);
    $product = Product::create([
        'name' => 'Game 1',
        'slug' => 'game-1',
        'availability' => 'Available',
        'price' => 10000,
        'description' => 'Description 1',
    ]);
    $item = CollectionItem::create([
        'collection_id' => $collection->id,
        'product_id' => $product->id,
        'position' => 1,
    ]);

    $key = 'collection__products_best-seller';
    $this->getJson('/api/v1/collections/best-seller/products')->assertOk();
    expect(Cache::tags(['public'])->has($key))->toBeTrue();

    $item->update(['position' => 2]);
    expect(Cache::tags(['public'])->has($key))->toBeFalse();

    $this->getJson('/api/v1/collections/best-seller/products')->assertOk();
    $item->delete();
    expect(Cache::tags(['public'])->has($key))->toBeFalse();
});

test('it excludes product by exclude_slug parameter', function () {
    $collection = Collection::factory()->create(['slug' => 'best-seller', 'title' => 'Best Seller']);

    $product1 = Product::create([
        'name' => 'Game 1',
        'slug' => 'game-1',
        'availability' => 'Available',
        'price' => 10000,
        'description' => 'Description 1',
    ]);

    $product2 = Product::create([
        'name' => 'Game 2',
        'slug' => 'game-2',
        'availability' => 'Available',
        'price' => 20000,
        'description' => 'Description 2',
    ]);

    CollectionItem::create([
        'collection_id' => $collection->id,
        'product_id' => $product1->id,
        'position' => 1,
    ]);

    CollectionItem::create([
        'collection_id' => $collection->id,
        'product_id' => $product2->id,
        'position' => 2,
    ]);

    $this->getJson('/api/v1/collections/best-seller/products?exclude_slug=game-1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'game-2');
});

test('it filters collection products by categories parameter', function () {
    $collection = Collection::factory()->create(['slug' => 'best-seller', 'title' => 'Best Seller']);
    $cat1 = Category::create(['name' => 'Family', 'slug' => 'family']);
    $cat2 = Category::create(['name' => 'Strategy', 'slug' => 'strategy']);

    $product1 = Product::create([
        'name' => 'Game 1',
        'slug' => 'game-1',
        'availability' => 'Available',
        'price' => 10000,
        'description' => 'Description 1',
    ]);
    $product1->categories()->attach($cat1);

    $product2 = Product::create([
        'name' => 'Game 2',
        'slug' => 'game-2',
        'availability' => 'Available',
        'price' => 20000,
        'description' => 'Description 2',
    ]);
    $product2->categories()->attach($cat2);

    CollectionItem::create(['collection_id' => $collection->id, 'product_id' => $product1->id, 'position' => 1]);
    CollectionItem::create(['collection_id' => $collection->id, 'product_id' => $product2->id, 'position' => 2]);

    $this->getJson('/api/v1/collections/best-seller/products?categories=family')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'game-1');
});
