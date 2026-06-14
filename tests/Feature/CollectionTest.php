<?php

use App\Filament\Resources\Collections\Pages\CreateCollection;
use App\Filament\Resources\Collections\Pages\EditCollection;
use App\Filament\Resources\Collections\Pages\ListCollections;
use App\Filament\Resources\Collections\RelationManagers\ItemsRelationManager;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    $this->actingAs($user);
});

it('can load the collection index page', function () {
    $collection = Collection::factory()->create();

    Livewire::test(ListCollections::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$collection]);
});

it('can load the collection create page', function () {
    Livewire::test(CreateCollection::class)
        ->assertOk();
});

it('can create a collection', function () {
    Livewire::test(CreateCollection::class)
        ->fillForm([
            'title' => 'New Arrival',
            'status' => 'active',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('collections', [
        'title' => 'New Arrival',
        'slug' => 'new-arrival',
        'status' => 'active',
        'position' => 1,
    ]);
});

it('enforces validation on collection title', function () {
    // Required check
    Livewire::test(CreateCollection::class)
        ->fillForm([
            'title' => '',
            'status' => 'active',
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);
});

it('can edit a collection', function () {
    $collection = Collection::factory()->create(['title' => 'Old Title']);

    Livewire::test(EditCollection::class, [
        'record' => $collection->getKey(),
    ])
        ->fillForm([
            'title' => 'New Title',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($collection->refresh()->title)->toBe('New Title');
});

it('can render and manage collection items using modals from the relation manager', function () {
    $collection = Collection::factory()->create();
    $product = Product::create([
        'name' => 'Magic Chess',
        'slug' => 'magic-chess',
        'availability' => 'Available',
        'price' => 100000,
        'description' => '<p>Description</p>',
    ]);

    // 1. Create Collection Item
    Livewire::test(ItemsRelationManager::class, [
        'ownerRecord' => $collection,
        'pageClass' => EditCollection::class,
    ])
        ->assertOk()
        ->callTableAction('create', data: [
            'product_id' => $product->id,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('collection_items', [
        'collection_id' => $collection->id,
        'product_id' => $product->id,
        'position' => 1,
    ]);

    $item = CollectionItem::where('collection_id', $collection->id)->where('product_id', $product->id)->first();
    expect($item)->not->toBeNull();

    // 2. We can view it (actually relation manager has edit action, but the edit action only modifies fields, here we don't have other fields besides product)
    // 3. Delete the Collection Item
    Livewire::test(ItemsRelationManager::class, [
        'ownerRecord' => $collection,
        'pageClass' => EditCollection::class,
    ])
        ->callTableAction(DeleteAction::class, record: $item)
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseMissing('collection_items', [
        'id' => $item->id,
    ]);
});

it('deletes collection items when collection is deleted', function () {
    $collection = Collection::factory()->create();
    $product = Product::create([
        'name' => 'Magic Chess',
        'slug' => 'magic-chess',
        'availability' => 'Available',
        'price' => 100000,
        'description' => '<p>Description</p>',
    ]);
    $item = CollectionItem::factory()->create([
        'collection_id' => $collection->id,
        'product_id' => $product->id,
    ]);

    expect(CollectionItem::count())->toBe(1);

    $collection->delete();

    expect(CollectionItem::count())->toBe(0);
});

it('deletes collection items when product is deleted', function () {
    $collection = Collection::factory()->create();
    $product = Product::create([
        'name' => 'Magic Chess',
        'slug' => 'magic-chess',
        'availability' => 'Available',
        'price' => 100000,
        'description' => '<p>Description</p>',
    ]);
    $item = CollectionItem::factory()->create([
        'collection_id' => $collection->id,
        'product_id' => $product->id,
    ]);

    expect(CollectionItem::count())->toBe(1);

    $product->delete();

    expect(CollectionItem::count())->toBe(0);
});
