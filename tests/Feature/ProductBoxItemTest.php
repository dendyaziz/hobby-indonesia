<?php

use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\RelationManagers\BoxItemsRelationManager;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBoxItem;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
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

    $this->category = Category::factory()->create(['name' => 'Board Games']);
    $this->product = Product::create([
        'name' => 'Saboteur',
        'slug' => 'saboteur',
        'availability' => 'Available',
        'price' => 150000,
        'description' => '<p>Fun game</p>',
        'difficulty' => 'Easy',
        'themes' => ['Abstract'],
    ]);
    $this->product->categories()->attach($this->category);
});

it('can manage box items via relation manager', function () {
    // 1. Create Box Item with predefined icon
    Livewire::test(BoxItemsRelationManager::class, [
        'ownerRecord' => $this->product,
        'pageClass' => EditProduct::class,
    ])
        ->assertOk()
        ->callTableAction('create', data: [
            'label' => '44 path cards',
            'icon_name' => 'waypoints',
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('product_box_items', [
        'product_id' => $this->product->id,
        'label' => '44 path cards',
        'icon_name' => 'waypoints',
        'position' => 1,
    ]);

    $item1 = ProductBoxItem::where('product_id', $this->product->id)->first();

    // 2. Create Box Item with custom upload
    Livewire::test(BoxItemsRelationManager::class, [
        'ownerRecord' => $this->product,
        'pageClass' => EditProduct::class,
    ])
        ->callTableAction('create', data: [
            'label' => 'Custom Dice',
            'icon_name' => 'custom',
            'image' => UploadedFile::fake()->image('custom_icon.png'),
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('product_box_items', [
        'product_id' => $this->product->id,
        'label' => 'Custom Dice',
        'icon_name' => 'custom',
        'position' => 2,
    ]);

    $item2 = ProductBoxItem::where('product_id', $this->product->id)->where('icon_name', 'custom')->first();
    expect($item2->getMedia('box-item-custom-icon'))->toHaveCount(1);

    // 3. Edit Box Item
    Livewire::test(BoxItemsRelationManager::class, [
        'ownerRecord' => $this->product,
        'pageClass' => EditProduct::class,
    ])
        ->callTableAction('edit', record: $item1, data: [
            'label' => '44 updated path cards',
            'icon_name' => 'gem',
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('product_box_items', [
        'id' => $item1->id,
        'label' => '44 updated path cards',
        'icon_name' => 'gem',
    ]);

    // 4. Delete Box Item
    Livewire::test(BoxItemsRelationManager::class, [
        'ownerRecord' => $this->product,
        'pageClass' => EditProduct::class,
    ])
        ->callTableAction(['edit', 'delete'], record: $item1)
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseMissing('product_box_items', [
        'id' => $item1->id,
    ]);
});

it('includes box items in product detail api response', function () {
    ProductBoxItem::create([
        'product_id' => $this->product->id,
        'label' => '44 path cards',
        'icon_name' => 'waypoints',
    ]);

    $customItem = ProductBoxItem::create([
        'product_id' => $this->product->id,
        'label' => 'Custom Tokens',
        'icon_name' => 'custom',
    ]);
    $customItem->addMedia(UploadedFile::fake()->image('token.png'))->toMediaCollection('box-item-custom-icon');

    $response = $this->getJson("/api/v1/products/{$this->product->slug}");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data.box_items')
        ->assertJsonPath('data.box_items.0.label', '44 path cards')
        ->assertJsonPath('data.box_items.0.icon_name', 'waypoints')
        ->assertJsonPath('data.box_items.0.icon_url', null)
        ->assertJsonPath('data.box_items.1.label', 'Custom Tokens')
        ->assertJsonPath('data.box_items.1.icon_name', 'custom');

    $boxItems = $response->json('data.box_items');
    expect($boxItems[1]['icon_url'])->not->toBeNull();
});
