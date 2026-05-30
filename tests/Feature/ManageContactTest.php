<?php

use App\Filament\Pages\ManageContact;
use App\Models\Contact;
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

it('can load the contact page', function () {
    Livewire::test(ManageContact::class)
        ->assertOk();
});

it('can save valid contact details', function () {
    $contact = Contact::firstOrCreate();

    Livewire::test(ManageContact::class)
        ->fillForm([
            'company_name' => 'Hobby Indonesia',
            'telephone' => '021-12345678',
            'email' => 'contact@hobbyindonesia.com',
            'address' => 'Jakarta, Indonesia',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('contacts', [
        'id' => $contact->id,
        'company_name' => 'Hobby Indonesia',
        'telephone' => '021-12345678',
        'email' => 'contact@hobbyindonesia.com',
        'address' => 'Jakarta, Indonesia',
    ]);
});

it('validates maximum lengths', function () {
    Livewire::test(ManageContact::class)
        ->fillForm([
            'company_name' => str_repeat('A', 51),
            'telephone' => str_repeat('1', 21),
            'email' => str_repeat('a', 42) . '@test.com', // 51 chars total
        ])
        ->call('save')
        ->assertHasFormErrors([
            'company_name',
            'telephone',
            'email',
        ]);
});

it('validates email format', function () {
    Livewire::test(ManageContact::class)
        ->fillForm([
            'email' => 'not-an-email',
        ])
        ->call('save')
        ->assertHasFormErrors(['email']);
});
