<?php

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Categories\RelationManagers\SubCategoriesRelationManager;
use App\Models\Category;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->create();
    $this->actingAs($user);
});

it('can load the category index page and displays only parents', function () {
    $parent = Category::factory()->create(['name' => 'Parent Category']);
    $sub = Category::factory()->create([
        'name' => 'Sub Category',
        'parent_category_id' => $parent->id,
    ]);

    Livewire::test(ListCategories::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$parent])
        ->assertCanNotSeeTableRecords([$sub]);
});

it('can load the category create page', function () {
    Livewire::test(CreateCategory::class)
        ->assertOk();
});

it('can create a parent category', function () {
    Livewire::test(CreateCategory::class)
        ->fillForm([
            'name' => 'Games',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'Games',
        'parent_category_id' => null,
    ]);
});

it('enforces validation on category name', function () {
    // Too long (31 characters)
    Livewire::test(CreateCategory::class)
        ->fillForm([
            'name' => str_repeat('a', 31),
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'max']);
});

it('can edit a parent category name', function () {
    $parent = Category::factory()->create(['name' => 'Old Name']);

    Livewire::test(EditCategory::class, [
        'record' => $parent->getKey(),
    ])
        ->fillForm([
            'name' => 'New Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($parent->refresh()->name)->toBe('New Name');
});

it('can render and manage subcategories using modals from the relation manager', function () {
    $parent = Category::factory()->create(['name' => 'Main Category']);

    // 1. Render and Create Subcategory via table create action
    Livewire::test(SubCategoriesRelationManager::class, [
        'ownerRecord' => $parent,
        'pageClass' => EditCategory::class,
    ])
        ->assertOk()
        ->callAction(TestAction::make('create')->table(), [
            'name' => 'First Sub',
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'First Sub',
        'parent_category_id' => $parent->id,
    ]);

    $sub = Category::where('name', 'First Sub')->first();
    expect($sub->parent_category_id)->toBe($parent->id);

    // 2. Edit the Subcategory
    Livewire::test(SubCategoriesRelationManager::class, [
        'ownerRecord' => $parent,
        'pageClass' => EditCategory::class,
    ])
        ->callAction(TestAction::make('edit')->table($sub), [
            'name' => 'Updated Sub',
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('categories', [
        'id' => $sub->id,
        'name' => 'Updated Sub',
    ]);

    // 3. Delete the Subcategory
    Livewire::test(SubCategoriesRelationManager::class, [
        'ownerRecord' => $parent,
        'pageClass' => EditCategory::class,
    ])
        ->callAction(TestAction::make(DeleteAction::class)->table($sub))
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseMissing('categories', [
        'id' => $sub->id,
    ]);
});

it('restricts deleting a parent category that has subcategories', function () {
    $parent = Category::factory()->create(['name' => 'Parent Category']);
    $sub = Category::factory()->create([
        'name' => 'Sub Category',
        'parent_category_id' => $parent->id,
    ]);

    $deleted = $parent->delete();

    expect($deleted)->toBeFalse();
    $this->assertDatabaseHas('categories', [
        'id' => $parent->id,
    ]);
});
