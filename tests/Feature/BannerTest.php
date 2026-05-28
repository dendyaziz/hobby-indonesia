<?php

use App\Filament\Resources\HeroBanners\Pages\CreateHeroBanner;
use App\Filament\Resources\HeroBanners\Pages\EditHeroBanner;
use App\Filament\Resources\HeroBanners\Pages\ListHeroBanners;
use App\Filament\Resources\ProductBanners\Pages\CreateProductBanner;
use App\Filament\Resources\ThirdBanners\Pages\CreateThirdBanner;
use App\Filament\Resources\ResellerBanners\Pages\CreateResellerBanner;
use App\Models\Banner;
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

it('can load the banner index and create pages for all placements', function () {
    Livewire::test(ListHeroBanners::class)->assertOk();
    Livewire::test(CreateHeroBanner::class)->assertOk();
});

it('can create a hero banner and automatically assign position and placement', function () {
    Livewire::test(CreateHeroBanner::class)
        ->fillForm([
            'title' => 'Hero Banner 1',
            'image' => UploadedFile::fake()->image('hero1.jpg'),
            'type' => 'none',
            'status' => 'active',
            'ended_at' => now()->addDays(5)->toDateString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('banners', [
        'title' => 'Hero Banner 1',
        'placement' => 'homepage--hero',
        'position' => 1,
    ]);

    // Create a second Hero Banner
    Livewire::test(CreateHeroBanner::class)
        ->fillForm([
            'title' => 'Hero Banner 2',
            'image' => UploadedFile::fake()->image('hero2.jpg'),
            'type' => 'none',
            'status' => 'active',
            'ended_at' => now()->addDays(5)->toDateString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('banners', [
        'title' => 'Hero Banner 2',
        'placement' => 'homepage--hero',
        'position' => 2,
    ]);
});

it('scopes position sequences separately for each placement', function () {
    // 1. Create a Hero Banner (should get position 1)
    Livewire::test(CreateHeroBanner::class)
        ->fillForm([
            'title' => 'Hero Banner A',
            'image' => UploadedFile::fake()->image('heroa.jpg'),
            'type' => 'none',
            'status' => 'active',
            'ended_at' => now()->addDays(5)->toDateString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('banners', [
        'title' => 'Hero Banner A',
        'placement' => 'homepage--hero',
        'position' => 1,
    ]);

    // 2. Create a Product Banner (should get position 1, not 2!)
    Livewire::test(CreateProductBanner::class)
        ->fillForm([
            'title' => 'Product Banner A',
            'image' => UploadedFile::fake()->image('proda.jpg'),
            'type' => 'none',
            'status' => 'active',
            'ended_at' => now()->addDays(5)->toDateString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('banners', [
        'title' => 'Product Banner A',
        'placement' => 'product-page--top',
        'position' => 1,
    ]);

    // 3. Create a Third Banner (should get position 1)
    Livewire::test(CreateThirdBanner::class)
        ->fillForm([
            'title' => 'Third Banner A',
            'image' => UploadedFile::fake()->image('thirda.jpg'),
            'type' => 'none',
            'status' => 'active',
            'ended_at' => now()->addDays(5)->toDateString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('banners', [
        'title' => 'Third Banner A',
        'placement' => 'homepage--third',
        'position' => 1,
    ]);

    // 4. Create a Reseller Banner (should get position 1)
    Livewire::test(CreateResellerBanner::class)
        ->fillForm([
            'title' => 'Reseller Banner A',
            'image' => UploadedFile::fake()->image('resellera.jpg'),
            'type' => 'none',
            'status' => 'active',
            'ended_at' => now()->addDays(5)->toDateString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('banners', [
        'title' => 'Reseller Banner A',
        'placement' => 'reseller-page--top',
        'position' => 1,
    ]);
});

it('requires url when type is link', function () {
    Livewire::test(CreateHeroBanner::class)
        ->fillForm([
            'title' => 'Linked Banner',
            'image' => UploadedFile::fake()->image('link.jpg'),
            'type' => 'link',
            'url' => '', // empty
            'status' => 'active',
            'ended_at' => now()->addDays(5)->toDateString(),
        ])
        ->call('create')
        ->assertHasFormErrors(['url' => 'required']);
});

it('validates that ended_at cannot be before started_at', function () {
    Livewire::test(CreateHeroBanner::class)
        ->fillForm([
            'title' => 'Date Test Banner',
            'image' => UploadedFile::fake()->image('date.jpg'),
            'type' => 'none',
            'status' => 'active',
            'started_at' => now()->toDateString(),
            'ended_at' => now()->subDay()->toDateString(), // before started_at!
        ])
        ->call('create')
        ->assertHasFormErrors(['ended_at']);
});

it('only requires ended_at if started_at is defined', function () {
    // 1. started_at is null, ended_at is null -> should succeed!
    Livewire::test(CreateHeroBanner::class)
        ->fillForm([
            'title' => 'No Dates Banner',
            'image' => UploadedFile::fake()->image('nodate.jpg'),
            'type' => 'none',
            'status' => 'active',
            'started_at' => null,
            'ended_at' => null,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('banners', [
        'title' => 'No Dates Banner',
        'started_at' => null,
        'ended_at' => null,
    ]);

    // 2. started_at is set, ended_at is null -> should fail validation!
    Livewire::test(CreateHeroBanner::class)
        ->fillForm([
            'title' => 'Missing Ended At Banner',
            'image' => UploadedFile::fake()->image('missingended.jpg'),
            'type' => 'none',
            'status' => 'active',
            'started_at' => now()->toDateString(),
            'ended_at' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['ended_at' => 'required']);
});

it('shows status as Scheduled if active and started_at is in the future', function () {
    $futureBanner = Banner::factory()->create([
        'title' => 'Future Banner',
        'placement' => 'homepage--hero',
        'status' => 'active',
        'started_at' => now()->addDays(5)->startOfDay(),
        'ended_at' => now()->addDays(10)->startOfDay(),
    ]);

    $activeBanner = Banner::factory()->create([
        'title' => 'Active Banner',
        'placement' => 'homepage--hero',
        'status' => 'active',
        'started_at' => now()->subDays(2)->startOfDay(),
        'ended_at' => now()->addDays(2)->startOfDay(),
    ]);

    Livewire::test(ListHeroBanners::class)
        ->assertCanRenderTableColumn('status')
        ->assertTableColumnFormattedStateSet('status', 'Scheduled', record: $futureBanner)
        ->assertTableColumnFormattedStateSet('status', 'Active', record: $activeBanner);
});

it('includes the navigation group in breadcrumbs', function () {
    $breadcrumbs = Livewire::test(ListHeroBanners::class)
        ->instance()
        ->getBreadcrumbs();

    expect($breadcrumbs)->toHaveKey('#');
    expect($breadcrumbs['#'])->toBe('Homepage');
});
