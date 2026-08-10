<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('it returns paginated products list', function () {
    Product::create([
        'name' => 'Game A',
        'slug' => 'game-a',
        'availability' => 'Available',
        'price' => 100000,
        'description' => 'Description A',
    ]);

    Product::create([
        'name' => 'Game B',
        'slug' => 'game-b',
        'availability' => 'Available',
        'price' => 200000,
        'description' => 'Description B',
    ]);

    $this->getJson('/api/v1/products?limit=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.last_page', 2);
});

test('it filters products by categories', function () {
    $category1 = Category::factory()->create(['name' => 'Strategy', 'slug' => 'strategy']);
    $category2 = Category::factory()->create(['name' => 'Family', 'slug' => 'family']);

    $product1 = Product::create([
        'name' => 'Game 1',
        'slug' => 'game-1',
        'availability' => 'Available',
        'price' => 100000,
        'description' => 'Description 1',
    ]);
    $product1->categories()->attach($category1);

    $product2 = Product::create([
        'name' => 'Game 2',
        'slug' => 'game-2',
        'availability' => 'Available',
        'price' => 200000,
        'description' => 'Description 2',
    ]);
    $product2->categories()->attach($category2);

    $this->getJson('/api/v1/products?categories=strategy')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Game 1');

    $this->getJson('/api/v1/products?categories=family')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Game 2');
});

test('it filters products by minimum age', function () {
    Product::create([
        'name' => 'Game Junior',
        'slug' => 'game-junior',
        'availability' => 'Available',
        'price' => 100000,
        'min_age' => 6,
        'description' => 'Junior game',
    ]);

    Product::create([
        'name' => 'Game Teen',
        'slug' => 'game-teen',
        'availability' => 'Available',
        'price' => 150000,
        'min_age' => 12,
        'description' => 'Teen game',
    ]);

    // min_age = 10, should return Game Teen (12 >= 10) but not Game Junior (6 < 10)
    $this->getJson('/api/v1/products?min_age=10')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Game Teen');
});

test('it filters products by playing time duration range', function () {
    Product::create([
        'name' => 'Game Short',
        'slug' => 'game-short',
        'availability' => 'Available',
        'price' => 100000,
        'playing_duration' => 15,
        'description' => 'Short game',
    ]);

    Product::create([
        'name' => 'Game Long',
        'slug' => 'game-long',
        'availability' => 'Available',
        'price' => 200000,
        'playing_duration' => 60,
        'description' => 'Long game',
    ]);

    $this->getJson('/api/v1/products?playing_time_from=10&playing_time_to=30')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Game Short');

    $this->getJson('/api/v1/products?playing_time_from=45')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Game Long');
});

test('it filters products by total players range', function () {
    Product::create([
        'name' => 'Game Duet',
        'slug' => 'game-duet',
        'availability' => 'Available',
        'price' => 100000,
        'min_player' => 2,
        'max_player' => 2,
        'description' => '2-player game',
    ]);

    Product::create([
        'name' => 'Game Party',
        'slug' => 'game-party',
        'availability' => 'Available',
        'price' => 200000,
        'min_player' => 4,
        'max_player' => 10,
        'description' => 'Party game',
    ]);

    $this->getJson('/api/v1/products?total_player_from=2&total_player_to=3')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Game Duet');

    $this->getJson('/api/v1/products?total_player_from=5')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Game Party');
});

test('it sorts products properly', function () {
    Product::create([
        'name' => 'Azul',
        'slug' => 'azul',
        'availability' => 'Available',
        'price' => 100000,
        'discounted_price' => 90000,
        'description' => 'Description Azul',
    ]);

    Product::create([
        'name' => 'Catan',
        'slug' => 'catan',
        'availability' => 'Available',
        'price' => 80000,
        'description' => 'Description Catan',
    ]);

    // Name A-Z
    $this->getJson('/api/v1/products?sort=nameAsc')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Azul')
        ->assertJsonPath('data.1.name', 'Catan');

    // Price Low to High (Catan 80k is lower than Azul discounted 90k)
    $this->getJson('/api/v1/products?sort=priceAsc')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Catan')
        ->assertJsonPath('data.1.name', 'Azul');

    // Price High to Low
    $this->getJson('/api/v1/products?sort=priceDesc')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Azul')
        ->assertJsonPath('data.1.name', 'Catan');
});

test('it caches products list data and flushes cache when product changes', function () {
    $product = Product::create([
        'name' => 'Game A',
        'slug' => 'game-a',
        'availability' => 'Available',
        'price' => 100000,
        'description' => 'Description A',
    ]);

    $hash = md5(json_encode([]));
    $key = "product__pagination_limit_8_page_1_hash_{$hash}";

    expect(Cache::tags(['public'])->has($key))->toBeFalse();

    $this->getJson('/api/v1/products')->assertOk();
    expect(Cache::tags(['public'])->has($key))->toBeTrue();

    $product->update(['name' => 'Updated Game A']);
    expect(Cache::tags(['public'])->has($key))->toBeFalse();
});

test('it returns single product detail by slug', function () {
    $category = Category::factory()->create(['name' => 'Strategy', 'slug' => 'strategy']);
    $product = Product::create([
        'name' => 'Brass: Birmingham',
        'slug' => 'brass-birmingham',
        'availability' => 'Available',
        'price' => 750000,
        'description' => 'Great strategic game',
        'youtube' => 'https://youtube.com/test',
        'playbook_url' => 'https://drive.google.com/file/d/test-playbook/view',
        'brand' => 'Amigo',
        'manufacture_country' => 'Germany',
        'publisher' => 'Publisher X',
        'designer' => 'Designer Y',
        'artist' => 'Artist Z',
    ]);
    $product->categories()->attach($category);

    $this->getJson('/api/v1/products/brass-birmingham')
        ->assertOk()
        ->assertJson([
            'data' => [
                'name' => 'Brass: Birmingham',
                'slug' => 'brass-birmingham',
                'availability' => 'Available',
                'price' => 750000,
                'description' => 'Great strategic game',
                'youtube' => 'https://youtube.com/test',
                'playbook_url' => 'https://drive.google.com/file/d/test-playbook/view',
                'brand' => 'Amigo',
                'manufacture_country' => 'Germany',
                'publisher' => 'Publisher X',
                'designer' => 'Designer Y',
                'artist' => 'Artist Z',
                'categories' => [
                    [
                        'name' => 'Strategy',
                        'slug' => 'strategy',
                    ],
                ],
            ],
        ]);
});

test('it returns 404 when product slug not found', function () {
    $this->getJson('/api/v1/products/non-existent')
        ->assertStatus(404);
});

test('it caches product detail and flushes cache when product changes', function () {
    $product = Product::create([
        'name' => 'Brass: Birmingham',
        'slug' => 'brass-birmingham',
        'availability' => 'Available',
        'price' => 750000,
        'description' => 'Great strategic game',
    ]);

    $key = 'product__detail_brass-birmingham';

    expect(Cache::tags(['public'])->has($key))->toBeFalse();

    $this->getJson('/api/v1/products/brass-birmingham')->assertOk();
    expect(Cache::tags(['public'])->has($key))->toBeTrue();

    $product->update(['name' => 'Updated Brass']);
    expect(Cache::tags(['public'])->has($key))->toBeFalse();
});

test('it filters products by difficulty', function () {
    Product::create([
        'name' => 'Game Easy',
        'slug' => 'game-easy',
        'availability' => 'Available',
        'price' => 100000,
        'difficulty' => 'Easy',
        'description' => 'Easy game description',
    ]);

    Product::create([
        'name' => 'Game Hard',
        'slug' => 'game-hard',
        'availability' => 'Available',
        'price' => 150000,
        'difficulty' => 'Hard',
        'description' => 'Hard game description',
    ]);

    $this->getJson('/api/v1/products?difficulty=Easy')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Game Easy');
});

test('it filters products by themes', function () {
    Product::create([
        'name' => 'Abstract Game',
        'slug' => 'abstract-game',
        'availability' => 'Available',
        'price' => 100000,
        'themes' => ['Abstract'],
        'description' => 'Abstract theme',
    ]);

    Product::create([
        'name' => 'Adventure Game',
        'slug' => 'adventure-game',
        'availability' => 'Available',
        'price' => 150000,
        'themes' => ['Adventure'],
        'description' => 'Adventure theme',
    ]);

    Product::create([
        'name' => 'Fantasy Game',
        'slug' => 'fantasy-game',
        'availability' => 'Available',
        'price' => 120000,
        'themes' => ['Fantasy'],
        'description' => 'Fantasy theme',
    ]);

    // Single theme
    $this->getJson('/api/v1/products?themes=Abstract')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Abstract Game');

    // Multiple themes (Abstract, Fantasy)
    $this->getJson('/api/v1/products?themes=Abstract,Fantasy')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'Abstract Game')
        ->assertJsonPath('data.1.name', 'Fantasy Game');
});

test('it returns themes list with counts', function () {
    Product::create([
        'name' => 'Game A',
        'slug' => 'game-a',
        'availability' => 'Available',
        'price' => 100000,
        'themes' => ['Abstract', 'Adventure'],
        'description' => 'Desc A',
    ]);

    Product::create([
        'name' => 'Game B',
        'slug' => 'game-b',
        'availability' => 'Available',
        'price' => 200000,
        'themes' => ['Abstract'],
        'description' => 'Desc B',
    ]);

    $this->getJson('/api/v1/themes')
        ->assertOk()
        ->assertJsonFragment([
            'name' => 'Abstract',
            'products_count' => 2,
        ])
        ->assertJsonFragment([
            'name' => 'Adventure',
            'products_count' => 1,
        ])
        ->assertJsonFragment([
            'name' => 'Horror',
            'products_count' => 0,
        ]);
});

test('it filters products by exclude_slug parameter', function () {
    Product::create([
        'name' => 'Game 1',
        'slug' => 'game-1',
        'availability' => 'Available',
        'price' => 10000,
        'description' => 'Description 1',
    ]);

    Product::create([
        'name' => 'Game 2',
        'slug' => 'game-2',
        'availability' => 'Available',
        'price' => 20000,
        'description' => 'Description 2',
    ]);

    $this->getJson('/api/v1/products?exclude_slug=game-1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'game-2');
});
