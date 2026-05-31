<?php

use App\Filament\Resources\Partners\Pages\CreatePartner;
use App\Filament\Resources\Partners\Pages\EditPartner;
use App\Filament\Resources\Partners\Pages\ListPartners;
use App\Models\Partner;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->create();
    $this->actingAs($user);
    Storage::fake('s3');
});

it('can load the partner index page', function () {
    Livewire::test(ListPartners::class)
        ->assertOk();
});

it('can load the partner create page', function () {
    Livewire::test(CreatePartner::class)
        ->assertOk();
});

it('can create a partner and auto increment position', function () {
    Livewire::test(CreatePartner::class)
        ->fillForm([
            'name' => 'Acme Partner',
            'image' => UploadedFile::fake()->image('partner1.jpg'),
            'status' => 'active',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('partners', [
        'name' => 'Acme Partner',
        'status' => 'active',
        'position' => 1,
    ]);

    // Create a second partner and ensure position increments to 2
    Livewire::test(CreatePartner::class)
        ->fillForm([
            'name' => 'Beta Partner',
            'image' => UploadedFile::fake()->image('partner2.jpg'),
            'status' => 'inactive',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('partners', [
        'name' => 'Beta Partner',
        'status' => 'inactive',
        'position' => 2,
    ]);
});

it('can edit a partner', function () {
    $partner = Partner::factory()->create([
        'name' => 'Original Partner Name',
        'status' => 'active',
    ]);
    $partner->addMedia(UploadedFile::fake()->image('partner.jpg'))->toMediaCollection('partners');

    Livewire::test(EditPartner::class, [
        'record' => $partner->getKey(),
    ])
        ->fillForm([
            'name' => 'Updated Partner Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($partner->refresh()->name)->toBe('Updated Partner Name');
});
