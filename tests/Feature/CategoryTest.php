<?php

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    $this->actingAs($user);
});

it('can load the category index page and displays categories', function () {
    $category = Category::factory()->create(['name' => 'Main Category']);

    Livewire::test(ListCategories::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$category]);
});

it('can load the category create page', function () {
    Livewire::test(CreateCategory::class)
        ->assertOk();
});

it('can create a category', function () {
    Livewire::test(CreateCategory::class)
        ->fillForm([
            'name' => 'Games',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'Games',
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

it('can edit a category name', function () {
    $category = Category::factory()->create(['name' => 'Old Name']);

    Livewire::test(EditCategory::class, [
        'record' => $category->getKey(),
    ])
        ->fillForm([
            'name' => 'New Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->refresh()->name)->toBe('New Name');
});

it('enforces uniqueness on category name', function () {
    Category::factory()->create(['name' => 'Strategy']);

    // Attempting to create category with duplicate name 'Strategy' should fail
    Livewire::test(CreateCategory::class)
        ->fillForm([
            'name' => 'Strategy',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique']);
});
