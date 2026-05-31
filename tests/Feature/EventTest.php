<?php

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Models\Event;
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

it('can load the event index and create pages', function () {
    Livewire::test(ListEvents::class)->assertOk();
    Livewire::test(CreateEvent::class)->assertOk();
});

it('can create an event with partners and save to database', function () {
    $partner1 = Partner::factory()->create(['name' => 'Partner One']);
    $partner2 = Partner::factory()->create(['name' => 'Partner Two']);

    Livewire::test(CreateEvent::class)
        ->fillForm([
            'title' => 'Spectacular Hobby Event',
            'image' => UploadedFile::fake()->image('event.jpg'),
            'description' => '<p>Join us for an exciting gaming convention!</p>',
            'partners' => [$partner1->id, $partner2->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('events', [
        'title' => 'Spectacular Hobby Event',
        'description' => '<p>Join us for an exciting gaming convention!</p>',
    ]);

    $event = Event::where('title', 'Spectacular Hobby Event')->first();
    expect($event)->not->toBeNull();
    expect($event->partners)->toHaveCount(2);
    expect($event->partners->pluck('id'))->toContain($partner1->id, $partner2->id);
});

it('validates required and maximum length constraints for title', function () {
    Livewire::test(CreateEvent::class)
        ->fillForm([
            'title' => '',
            'description' => '<p>Test description</p>',
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);

    Livewire::test(CreateEvent::class)
        ->fillForm([
            'title' => str_repeat('a', 101),
            'description' => '<p>Test description</p>',
        ])
        ->call('create')
        ->assertHasFormErrors(['title']);
});

it('can edit and update an event and its partners', function () {
    $partner1 = Partner::factory()->create(['name' => 'Original Partner']);
    $partner2 = Partner::factory()->create(['name' => 'New Partner']);

    $event = Event::factory()->create([
        'title' => 'Original Title',
        'description' => '<p>Original description</p>',
    ]);
    $event->partners()->attach($partner1);

    Livewire::test(EditEvent::class, [
        'record' => $event->getKey(),
    ])
        ->assertFormSet([
            'title' => 'Original Title',
            'description' => '<p>Original description</p>',
        ])
        ->fillForm([
            'title' => 'Updated Title',
            'description' => '<p>Updated description</p>',
            'partners' => [$partner2->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'title' => 'Updated Title',
        'description' => '<p>Updated description</p>',
    ]);

    $event->refresh();
    expect($event->partners)->toHaveCount(1);
    expect($event->partners->first()->id)->toBe($partner2->id);
});

it('includes the navigation group in breadcrumbs', function () {
    $breadcrumbs = Livewire::test(ListEvents::class)
        ->instance()
        ->getBreadcrumbs();

    expect($breadcrumbs)->toHaveKey('#');
    expect($breadcrumbs['#'])->toBe('Reseller');
});
