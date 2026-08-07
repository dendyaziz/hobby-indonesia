<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BoxItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'boxItems';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('label')
                    ->required()
                    ->maxLength(100),

                ViewField::make('icon_name')
                    ->label('Icon')
                    ->view('filament.forms.components.box-icon-picker')
                    ->default('waypoints')
                    ->required()
                    ->live(),

                SpatieMediaLibraryFileUpload::make('image')
                    ->label('Custom Icon Image')
                    ->collection('box-item-custom-icon')
                    ->image()
                    ->imageEditor()
                    ->imagePreviewHeight(250)
                    ->visible(fn (Get $get) => $get('icon_name') === 'custom')
                    ->required(fn (Get $get) => $get('icon_name') === 'custom'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel('Box Item')
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('position')
                    ->label('Sequence')
                    ->prefix('#')
                    ->sortable(),
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('box-item-custom-icon')
                    ->conversion('small')
                    ->label('Custom Icon'),
                TextColumn::make('icon_name')
                    ->label('Icon Type')
                    ->formatStateUsing(fn (string $state) => ucfirst(str_replace('-', ' ', $state))),
                TextColumn::make('label')
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
