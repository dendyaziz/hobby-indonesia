<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GuidesRelationManager extends RelationManager
{
    protected static string $relationship = 'guides';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('guide-image')
                    ->image()
                    ->imageEditor()
                    ->imagePreviewHeight(250)
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(100),
                Textarea::make('description')
                    ->rows(3)
                    ->maxLength(1000)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel('Guide')
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('position')
                    ->label('Sequence')
                    ->prefix('#')
                    ->sortable(),
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('guide-image')
                    ->conversion('small'),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
            ])
            ->reorderable('position')
            ->defaultSort('position')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->extraModalFooterActions([
                        DeleteAction::make(),
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
