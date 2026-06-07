<?php

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->create();
    $this->actingAs($user);
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
            'youtube' => '@hobby_indo_channel',
            'description' => '<p>This is a super fun game for the whole family.</p>',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('products', [
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
        'youtube' => '@hobby_indo_channel',
        'description' => '<p>This is a super fun game for the whole family.</p>',
    ]);
});

it('validates required fields', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => '',
            'availability' => '',
            'price' => null,
            'description' => '',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'availability' => 'required',
            'price' => 'required',
            'description',
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
    // Invalid Youtube URLs
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'youtube' => 'https://facebook.com/mychan',
        ])
        ->call('create')
        ->assertHasFormErrors(['youtube']);

    // Invalid Youtube Handle (too short/invalid char)
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'youtube' => '@a',
        ])
        ->call('create')
        ->assertHasFormErrors(['youtube']);

    // Valid inputs
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Board Game',
            'availability' => 'Available',
            'price' => 100000,
            'description' => 'Cool game',
            'youtube' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});
