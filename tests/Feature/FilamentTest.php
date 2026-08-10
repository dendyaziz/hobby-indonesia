<?php

use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('has a login page for filament', function () {
    $response = $this->get('/admin/login');

    $response->assertStatus(200);
});

it('allows registered admin users to log in', function () {
    $user = User::factory()->create([
        'email' => 'admin@hobbyindonesia.com',
        'password' => bcrypt('password'),
    ]);

    Livewire::test(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertRedirect('/admin');

    $this->assertAuthenticatedAs($user);
});
