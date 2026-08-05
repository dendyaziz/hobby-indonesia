<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CharactersRelationManager extends RelationManager
{
    protected static string $relationship = 'characters';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('character-image')
                    ->image()
                    ->imageEditor()
                    ->imagePreviewHeight(250)
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(100),
                Textarea::make('description')
                    ->rows(3)
                    ->maxLength(500),
                ColorPicker::make('background_color')
                    ->label('Background color')
                    ->default('#FFFFFF'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel('Character')
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('position')
                    ->label('Sequence')
                    ->prefix('#')
                    ->sortable(),
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('character-image')
                    ->conversion('small'),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                ColorColumn::make('background_color')
                    ->label('Color'),
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
