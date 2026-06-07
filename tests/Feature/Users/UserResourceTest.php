<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can render the user list page', function () {
    $users = User::factory()->count(3)->create();

    Livewire::test(ListUsers::class)
        ->assertOk()
        ->assertCanSeeTableRecords($users);
});

it('can filter and display soft-deleted users in the list page', function () {
    $activeUser = User::factory()->create();
    $trashedUser = User::factory()->create();
    $trashedUser->delete();

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords([$activeUser])
        ->assertCanNotSeeTableRecords([$trashedUser])
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords([$trashedUser]);
});

it('correctly computes status column for users', function () {
    $activeUser = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $invitedUser = User::factory()->create([
        'password' => null,
    ]);

    $deletedUser = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);
    $deletedUser->delete();

    Livewire::test(ListUsers::class)
        ->filterTable('trashed', true)
        ->assertTableColumnStateSet('status', 'active', record: $activeUser)
        ->assertTableColumnStateSet('status', 'invited', record: $invitedUser)
        ->assertTableColumnStateSet('status', 'deleted', record: $deletedUser);
});

it('can create a user', function () {
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $user = User::where('email', 'john@example.com')->first();
    $this->assertNotNull($user->password);
});

it('can edit a user', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
    ]);

    Livewire::test(EditUser::class, [
        'record' => $user->getKey(),
    ])
        ->fillForm([
            'name' => 'New Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
    ]);
});

it('can soft delete a user', function () {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->callAction(DeleteAction::class)
        ->assertNotified();

    $this->assertSoftDeleted($user);
});

it('can restore a soft-deleted user', function () {
    $user = User::factory()->create();
    $user->delete();

    Livewire::test(EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->callAction(RestoreAction::class)
        ->assertNotified();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'deleted_at' => null,
    ]);
});
