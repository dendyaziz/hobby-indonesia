<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(function ($record): string {
                        if ($record->trashed()) {
                            return 'deleted';
                        }
                        if ($record->password !== null && $record->password !== '') {
                            return 'active';
                        }
                        return 'invited';
                    })
                    ->formatStateUsing(fn (string $state): string => \Illuminate\Support\Str::headline($state))
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'deleted' => 'danger',
                        'invited' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Registered at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
