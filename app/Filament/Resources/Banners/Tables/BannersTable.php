<?php

namespace App\Filament\Resources\Banners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')
                    ->label('Sequence')
                    ->prefix('#')
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('banners')
                    ->conversion('small'),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state, \App\Models\Banner $record): string =>
                        match (true) {
                            $state === 'active' && $record->end_date && $record->end_date->isPast() => 'Expired',
                            $state === 'active' && $record->start_date && $record->start_date->isFuture() => 'Scheduled',
                            default => Str::headline($state),
                        }
                    )
                    ->color(fn (string $state, \App\Models\Banner $record): string =>
                        match (true) {
                            $state === 'active' && $record->end_date && $record->end_date->isPast() => 'danger',
                            $state === 'active' && $record->start_date && $record->start_date->isFuture() => 'warning',
                            $state === 'active' => 'success',
                            $state === 'inactive' => 'danger',
                            default => 'gray',
                        }
                    ),

                TextColumn::make('start_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
