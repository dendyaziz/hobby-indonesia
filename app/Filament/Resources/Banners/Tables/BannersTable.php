<?php

namespace App\Filament\Resources\Banners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
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

                ImageColumn::make('image'),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Str::headline($state)),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state, \App\Models\Banner $record): string => 
                        $state === 'active' && $record->started_at && $record->started_at->isFuture()
                            ? 'Scheduled'
                            : Str::headline($state)
                    )
                    ->color(fn (string $state, \App\Models\Banner $record): string => 
                        $state === 'active' && $record->started_at && $record->started_at->isFuture()
                            ? 'warning'
                            : match (strtolower($state)) {
                                'active' => 'success',
                                'inactive' => 'danger',
                                default => 'gray',
                            }
                    ),

                TextColumn::make('started_at')
                    ->date()
                    ->sortable(),

                TextColumn::make('ended_at')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->filters([
                //
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
