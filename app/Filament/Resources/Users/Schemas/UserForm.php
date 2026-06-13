<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class, 'email', ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn ($state) => filled($state))
                    ->rule(Password::default())
                    ->hiddenOn('create')
                    ->visible(fn (?User $record): bool => $record?->id === auth()->id())
                    ->maxLength(255),
                Select::make('roles')
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query, ?User $record) => $query->when(
                            ! ($record?->hasRole('Super Admin') ?? false),
                            fn (Builder $q) => $q->where('name', '!=', 'Super Admin')
                        )
                    )
                    ->multiple()
                    ->required(fn (?User $record): bool => ! ($record?->hasRole('Super Admin') ?? false))
                    ->preload()
                    ->searchable()
                    ->disabled(fn (?User $record): bool => $record?->hasRole('Super Admin') ?? false),
            ]);
    }
}
