<?php

use App\Filament\Resources\Testimonies\Pages\CreateTestimony;
use App\Filament\Resources\Testimonies\Pages\EditTestimony;
use App\Filament\Resources\Testimonies\Pages\ListTestimonies;
use App\Models\Testimony;
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

it('can load the testimony index page', function () {
    Livewire::test(ListTestimonies::class)
        ->assertOk();
});

it('can load the testimony create page', function () {
    Livewire::test(CreateTestimony::class)
        ->assertOk();
});

it('can create a testimony', function () {
    Livewire::test(CreateTestimony::class)
        ->fillForm([
            'name' => 'John Doe',
            'testimony' => '<strong>Great experience!</strong> Highly recommended.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('testimonies', [
        'name' => 'John Doe',
        'testimony' => '<strong>Great experience!</strong> Highly recommended.',
    ]);
});

it('can edit a testimony', function () {
    $testimony = Testimony::factory()->create([
        'name' => 'Jane Smith',
        'testimony' => 'Nice work.',
    ]);

    Livewire::test(EditTestimony::class, [
        'record' => $testimony->getKey(),
    ])
        ->fillForm([
            'name' => 'Jane Smith Updated',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($testimony->refresh()->name)->toBe('Jane Smith Updated');
});
