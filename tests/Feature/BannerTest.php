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

it('can create a hero banner and automatically assign position and placement without dates', function () {
    Livewire::test(CreateHeroBanner::class)
        ->fillForm([
            'title' => 'Hero Banner 1',
            'image' => UploadedFile::fake()->image('hero1.jpg'),
            'type' => 'none',
            'status' => 'active',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('banners', [
        'title' => 'Hero Banner 1',
        'placement' => 'homepage--hero',
        'position' => 1,
        'start_date' => null,
        'end_date' => null,
    ]);

    // Create a second Hero Banner without dates
    Livewire::test(CreateHeroBanner::class)
        ->fillForm([
            'title' => 'Hero Banner 2',
            'image' => UploadedFile::fake()->image('hero2.jpg'),
            'type' => 'none',
            'status' => 'active',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('banners', [
        'title' => 'Hero Banner 2',
        'placement' => 'homepage--hero',
        'position' => 2,
        'start_date' => null,
        'end_date' => null,
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
        ])
        ->call('create')
        ->assertHasFormErrors(['url' => 'required']);
});

it('validates that end_date cannot be before start_date', function () {
    Livewire::test(CreateHeroBanner::class)
        ->fillForm([
            'title' => 'Date Test Banner',
            'image' => UploadedFile::fake()->image('date.jpg'),
            'type' => 'none',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'end_date' => now()->subDay()->toDateString(), // before start_date!
        ])
        ->call('create')
        ->assertHasFormErrors(['end_date']);
});

it('allows start_date to be set while end_date remains null (indefinitely active)', function () {
    Livewire::test(CreateHeroBanner::class)
        ->fillForm([
            'title' => 'Indefinitely Scheduled Banner',
            'image' => UploadedFile::fake()->image('indefinite.jpg'),
            'type' => 'none',
            'status' => 'active',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => null, // always active after start date
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('banners', [
        'title' => 'Indefinitely Scheduled Banner',
        'start_date' => now()->addDays(5)->toDateString() . ' 00:00:00',
        'end_date' => null,
    ]);
});

it('allows end_date to be set while start_date remains null (scheduled end only)', function () {
    Livewire::test(CreateHeroBanner::class)
        ->fillForm([
            'title' => 'Scheduled End Only Banner',
            'image' => UploadedFile::fake()->image('endonly.jpg'),
            'type' => 'none',
            'status' => 'active',
            'start_date' => null,
            'end_date' => now()->addDays(5)->toDateString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('banners', [
        'title' => 'Scheduled End Only Banner',
        'start_date' => null,
        'end_date' => now()->addDays(5)->toDateString() . ' 00:00:00',
    ]);
});

it('shows status as Scheduled if active and start_date is in the future', function () {
    $futureBanner = Banner::factory()->create([
        'title' => 'Future Banner',
        'placement' => 'homepage--hero',
        'status' => 'active',
        'start_date' => now()->addDays(5)->startOfDay(),
        'end_date' => null,
    ]);

    $activeBanner = Banner::factory()->create([
        'title' => 'Active Banner',
        'placement' => 'homepage--hero',
        'status' => 'active',
        'start_date' => now()->subDays(2)->startOfDay(),
        'end_date' => now()->addDays(2)->startOfDay(),
    ]);

    Livewire::test(ListHeroBanners::class)
        ->assertCanRenderTableColumn('status')
        ->assertTableColumnFormattedStateSet('status', 'Scheduled', record: $futureBanner)
        ->assertTableColumnFormattedStateSet('status', 'Active', record: $activeBanner);
});

it('shows status as Expired if active and end_date is in the past', function () {
    $expiredBanner = Banner::factory()->create([
        'title' => 'Expired Banner',
        'placement' => 'homepage--hero',
        'status' => 'active',
        'start_date' => now()->subDays(5)->startOfDay(),
        'end_date' => now()->subDays(1)->startOfDay(),
    ]);

    Livewire::test(ListHeroBanners::class)
        ->assertCanRenderTableColumn('status')
        ->assertTableColumnFormattedStateSet('status', 'Expired', record: $expiredBanner);
});

it('includes the navigation group in breadcrumbs', function () {
    $breadcrumbs = Livewire::test(ListHeroBanners::class)
        ->instance()
        ->getBreadcrumbs();

    expect($breadcrumbs)->toHaveKey('#');
    expect($breadcrumbs['#'])->toBe('Homepage');
});
