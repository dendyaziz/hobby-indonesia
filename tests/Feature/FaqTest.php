<?php

use App\Filament\Resources\Faqs\Pages\CreateFaq;
use App\Filament\Resources\Faqs\Pages\EditFaq;
use App\Filament\Resources\Faqs\Pages\ListFaqs;
use App\Models\Faq;
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

it('can load the faq index page', function () {
    Livewire::test(ListFaqs::class)
        ->assertOk();
});

it('can load the faq create page', function () {
    Livewire::test(CreateFaq::class)
        ->assertOk();
});

it('can create a faq', function () {
    Livewire::test(CreateFaq::class)
        ->fillForm([
            'question' => 'What is Hobby Indonesia?',
            'answer' => '<strong>Hobby Indonesia</strong> is an Indonesian community hub for hobbies.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('faqs', [
        'question' => 'What is Hobby Indonesia?',
        'answer' => '<strong>Hobby Indonesia</strong> is an Indonesian community hub for hobbies.',
    ]);
});

it('can edit a faq', function () {
    $faq = Faq::factory()->create([
        'question' => 'Original Question',
        'answer' => 'Original Answer',
    ]);

    Livewire::test(EditFaq::class, [
        'record' => $faq->getKey(),
    ])
        ->fillForm([
            'question' => 'Updated Question',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($faq->refresh()->question)->toBe('Updated Question');
});
