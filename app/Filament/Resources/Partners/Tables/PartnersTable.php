<?php

namespace App\Filament\Resources\Partners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')
                    ->label('Sequence')
                    ->prefix('#')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('partners')
                    ->conversion('small'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
