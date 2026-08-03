<?php

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    $this->actingAs($user);
    Storage::fake('s3');

    $this->parentCategory = Category::factory()->create(['name' => 'Board Games']);
    $this->subCategory = Category::factory()->create([
        'name' => 'Strategy',
    ]);
});

it('can load the product index and create pages', function () {
    Livewire::test(ListProducts::class)->assertOk();
    Livewire::test(CreateProduct::class)->assertOk();
});

it('can create a product and save to database', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Amazing Board Game',
            'availability' => 'Available',
            'price' => 250000,
            'discount_percentage' => 10.50,
            'discounted_price' => 223750,
            'brand' => 'Hobby ID Brand',
            'manufacture_country' => 'Indonesia',
            'publisher' => 'Indo Games',
            'designer' => 'John Doe',
            'artist' => 'Jane Smith',
            'min_age' => 10,
            'min_player' => 2,
            'max_player' => 4,
            'playing_duration' => 60,
            'youtube' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'tiktok_videos' => [
                ['url' => 'https://www.tiktok.com/@username/video/7391234567890123456'],
                ['url' => 'https://vt.tiktok.com/ZS23456/'],
            ],
            'description' => '<p>This is a super fun game for the whole family.</p>',
            'difficulty' => 'Easy',
            'themes' => ['Abstract', 'Animals'],
            'images' => [
                UploadedFile::fake()->image('product1.jpg'),
                UploadedFile::fake()->image('product2.jpg'),
            ],
            'categories' => [$this->subCategory->id],
            'tags' => ['Strategy', 'Board Game'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('products', [
        'name' => 'Amazing Board Game',
        'slug' => 'amazing-board-game',
        'availability' => 'Available',
        'price' => 250000,
        'discount_percentage' => 10.50,
        'discounted_price' => 223750,
        'brand' => 'Hobby ID Brand',
        'manufacture_country' => 'Indonesia',
        'publisher' => 'Indo Games',
        'designer' => 'John Doe',
        'artist' => 'Jane Smith',
        'min_age' => 10,
        'min_player' => 2,
        'max_player' => 4,
        'playing_duration' => 60,
        'youtube' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'description' => '<p>This is a super fun game for the whole family.</p>',
        'difficulty' => 'Easy',
        'themes' => json_encode(['Abstract', 'Animals']),
    ]);

    $product = Product::first();
    expect($product->getMedia('product-images'))->toHaveCount(2);
    expect($product->categories)->toHaveCount(1);
    expect($product->categories->first()->id)->toBe($this->subCategory->id);
    expect($product->tags->pluck('name')->toArray())->toContain('Strategy', 'Board Game');
    expect($product->tiktok_videos)->toBe([
        'https://www.tiktok.com/@username/video/7391234567890123456',
        'https://vt.tiktok.com/ZS23456/',
    ]);
});

it('validates required fields', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => '',
            'availability' => '',
            'price' => null,
            'description' => '',
            'difficulty' => '',
            'themes' => [],
            'images' => [],
            'categories' => [],
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'availability' => 'required',
            'price' => 'required',
            'description',
            'difficulty' => 'required',
            'themes' => 'required',
            'images' => 'required',
            'categories' => 'required',
        ]);
});

it('validates maximum lengths of fields', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => str_repeat('a', 101),
            'brand' => str_repeat('b', 51),
            'manufacture_country' => str_repeat('c', 51),
            'publisher' => str_repeat('d', 51),
            'designer' => str_repeat('e', 51),
            'artist' => str_repeat('f', 51),
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name',
            'brand',
            'manufacture_country',
            'publisher',
            'designer',
            'artist',
        ]);
});

it('validates min_age, min_player, max_player, playing_duration', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'min_age' => -1,
            'min_player' => 0,
            'max_player' => 0,
            'playing_duration' => 0,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'min_age',
            'min_player',
            'max_player',
            'playing_duration',
        ]);

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'min_age' => 51,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'min_age',
        ]);
});

it('validates youtube validation rules', function () {
    // Invalid Youtube URLs (e.g. facebook URL)
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'youtube' => 'https://facebook.com/mychan',
        ])
        ->call('create')
        ->assertHasFormErrors(['youtube']);

    // Invalid Youtube Handle (must reject channel handles)
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'youtube' => '@hobby_indo_channel',
        ])
        ->call('create')
        ->assertHasFormErrors(['youtube']);

    // Invalid Youtube Channel URL
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'youtube' => 'https://www.youtube.com/channel/UCabcdefghijklmnopqr',
        ])
        ->call('create')
        ->assertHasFormErrors(['youtube']);

    // Valid inputs: standard watch URL
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Board Game',
            'availability' => 'Available',
            'price' => 100000,
            'description' => 'Cool game',
            'difficulty' => 'Easy',
            'themes' => ['Abstract'],
            'youtube' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'images' => [
                UploadedFile::fake()->image('product_test.jpg'),
            ],
            'categories' => [$this->subCategory->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Valid inputs: short youtu.be URL with parameters
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Board Game 2',
            'availability' => 'Available',
            'price' => 100000,
            'description' => 'Cool game 2',
            'difficulty' => 'Easy',
            'themes' => ['Abstract'],
            'youtube' => 'https://youtu.be/dQw4w9WgXcQ?t=10',
            'images' => [
                UploadedFile::fake()->image('product_test2.jpg'),
            ],
            'categories' => [$this->subCategory->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Valid inputs: embed URL
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Board Game 3',
            'availability' => 'Available',
            'price' => 100000,
            'description' => 'Cool game 3',
            'difficulty' => 'Easy',
            'themes' => ['Abstract'],
            'youtube' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'images' => [
                UploadedFile::fake()->image('product_test3.jpg'),
            ],
            'categories' => [$this->subCategory->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('validates tiktok_videos validation rules', function () {
    $undoRepeaterFake = Repeater::fake();

    // Invalid TikTok URL (e.g. non-tiktok URL)
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'tiktok_videos' => [
                ['url' => 'https://facebook.com/video/123'],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['tiktok_videos.0.url']);

    // Valid TikTok URLs
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Board Game',
            'availability' => 'Available',
            'price' => 100000,
            'description' => 'Cool game',
            'difficulty' => 'Easy',
            'themes' => ['Abstract'],
            'tiktok_videos' => [
                ['url' => 'https://www.tiktok.com/@user/video/1234567890'],
                ['url' => 'https://vt.tiktok.com/ZS23456/'],
            ],
            'images' => [
                UploadedFile::fake()->image('product_test.jpg'),
            ],
            'categories' => [$this->subCategory->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $undoRepeaterFake();
});

it('synchronizes price, discount percentage, and discounted price in real-time', function () {
    // 1. Setting price and discount percentage calculates discounted price
    Livewire::test(CreateProduct::class)
        ->set('data.price', 100000)
        ->set('data.discount_percentage', 15)
        ->assertFormSet([
            'discounted_price' => 85000,
        ]);

    // 2. Setting price and discounted price calculates discount percentage
    Livewire::test(CreateProduct::class)
        ->set('data.price', 100000)
        ->set('data.discounted_price', 75000)
        ->assertFormSet([
            'discount_percentage' => 25,
        ]);

    // 3. Setting price, then clearing it, clears discount and discounted price
    Livewire::test(CreateProduct::class)
        ->set('data.price', 100000)
        ->set('data.discount_percentage', 15)
        ->set('data.price', null)
        ->assertFormSet([
            'discount_percentage' => null,
            'discounted_price' => null,
        ]);
});

it('can edit and update a product and its categories', function () {
    $newSubCategory = Category::factory()->create([
        'name' => 'Card Games',
    ]);

    $product = Product::create([
        'name' => 'Original Name',
        'slug' => 'original-name',
        'availability' => 'Available',
        'price' => 100000,
        'description' => '<p>Original description</p>',
        'difficulty' => 'Easy',
        'themes' => ['Abstract'],
    ]);
    $product->categories()->attach($this->subCategory);
    $product->attachTags(['OldTag1', 'OldTag2']);

    Livewire::test(EditProduct::class, [
        'record' => $product->getKey(),
    ])
        ->assertFormSet([
            'name' => 'Original Name',
            'availability' => 'Available',
            'price' => 100000,
            'description' => '<p>Original description</p>',
            'difficulty' => 'Easy',
            'themes' => ['Abstract'],
            'tags' => ['OldTag1', 'OldTag2'],
        ])
        ->fillForm([
            'name' => 'Updated Name',
            'availability' => 'Available',
            'price' => 120000,
            'description' => '<p>Updated description</p>',
            'difficulty' => 'Medium',
            'themes' => ['Animals'],
            'images' => [
                UploadedFile::fake()->image('updated1.jpg'),
            ],
            'categories' => [$newSubCategory->id],
            'tags' => ['UpdatedTag'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Name',
        'price' => 120000,
        'difficulty' => 'Medium',
        'themes' => json_encode(['Animals']),
    ]);

    $product->refresh();
    expect($product->categories)->toHaveCount(1);
    expect($product->categories->first()->id)->toBe($newSubCategory->id);
    expect($product->tags->pluck('name')->toArray())->toBe(['UpdatedTag']);
});

it('validates unique constraint for slug', function () {
    Product::create([
        'name' => 'Original Game',
        'slug' => 'duplicate-slug',
        'availability' => 'Available',
        'price' => 100000,
        'description' => 'Description',
        'difficulty' => 'Easy',
    ]);

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Another Game Name',
            'availability' => 'Available',
            'price' => 100000,
            'description' => 'Description',
            'difficulty' => 'Easy',
            'images' => [
                UploadedFile::fake()->image('product.jpg'),
            ],
            'categories' => [$this->subCategory->id],
        ])
        ->set('data.slug', 'duplicate-slug')
        ->call('create')
        ->assertHasFormErrors(['slug']);
});
