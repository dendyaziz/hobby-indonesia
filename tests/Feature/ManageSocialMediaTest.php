<?php

use App\Filament\Pages\ManageSocialMedia;
use App\Models\SocialMedia;
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

it('can load the social media page', function () {
    Livewire::test(ManageSocialMedia::class)
        ->assertOk();
});

it('can save valid social media handles and URLs', function () {
    $socialMedia = SocialMedia::firstOrCreate();

    Livewire::test(ManageSocialMedia::class)
        ->fillForm([
            'facebook' => 'john.doe.123',
            'instagram' => '@johndoe',
            'tiktok' => '@johndoe',
            'youtube' => 'UCabcdefghijklmnopqrstuvw',
            'x' => 'johndoe_1',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('social_medias', [
        'id' => $socialMedia->id,
        'facebook' => 'john.doe.123',
        'instagram' => '@johndoe',
        'youtube' => 'UCabcdefghijklmnopqrstuvw',
        'x' => 'johndoe_1',
    ]);

    Livewire::test(ManageSocialMedia::class)
        ->fillForm([
            'facebook' => 'https://facebook.com/myaccount',
            'instagram' => 'https://www.instagram.com/myaccount',
            'tiktok' => 'https://www.tiktok.com/@myaccount',
            'youtube' => 'https://youtube.com/@mychannel',
            'x' => 'https://twitter.com/myaccount',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('social_medias', [
        'id' => $socialMedia->id,
        'facebook' => 'https://facebook.com/myaccount',
        'instagram' => 'https://www.instagram.com/myaccount',
        'youtube' => 'https://youtube.com/@mychannel',
        'x' => 'https://twitter.com/myaccount',
    ]);
});

it('fails validation on invalid tiktok inputs', function () {
    Livewire::test(ManageSocialMedia::class)
        ->fillForm(['tiktok' => '@a'])
        ->call('save')
        ->assertHasFormErrors(['tiktok']);

    Livewire::test(ManageSocialMedia::class)
        ->fillForm(['tiktok' => 'johndoe'])
        ->call('save')
        ->assertHasFormErrors(['tiktok']);

    Livewire::test(ManageSocialMedia::class)
        ->fillForm(['tiktok' => 'https://tiktok.com/'])
        ->call('save')
        ->assertHasFormErrors(['tiktok']);
});

it('fails validation on invalid facebook inputs', function () {
    Livewire::test(ManageSocialMedia::class)
        ->fillForm(['facebook' => 'abcd'])
        ->call('save')
        ->assertHasFormErrors(['facebook']);

    Livewire::test(ManageSocialMedia::class)
        ->fillForm(['facebook' => 'https://google.com/username'])
        ->call('save')
        ->assertHasFormErrors(['facebook']);
});

it('fails validation on invalid instagram inputs', function () {
    Livewire::test(ManageSocialMedia::class)
        ->fillForm(['instagram' => 'user.name.is.way.too.long.for.instagram.validation.rules'])
        ->call('save')
        ->assertHasFormErrors(['instagram']);

    Livewire::test(ManageSocialMedia::class)
        ->fillForm(['instagram' => 'username.'])
        ->call('save')
        ->assertHasFormErrors(['instagram']);
});

it('fails validation on invalid youtube inputs', function () {
    Livewire::test(ManageSocialMedia::class)
        ->fillForm(['youtube' => 'ab'])
        ->call('save')
        ->assertHasFormErrors(['youtube']);

    Livewire::test(ManageSocialMedia::class)
        ->fillForm(['youtube' => 'https://youtube.com/'])
        ->call('save')
        ->assertHasFormErrors(['youtube']);
});

it('fails validation on invalid x inputs', function () {
    Livewire::test(ManageSocialMedia::class)
        ->fillForm(['x' => 'way_too_long_for_twitter_handle'])
        ->call('save')
        ->assertHasFormErrors(['x']);

    Livewire::test(ManageSocialMedia::class)
        ->fillForm(['x' => 'https://twitter.com/'])
        ->call('save')
        ->assertHasFormErrors(['x']);
});
