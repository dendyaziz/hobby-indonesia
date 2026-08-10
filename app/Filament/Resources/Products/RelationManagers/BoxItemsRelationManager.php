<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\File;

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

                Radio::make('icon_source')
                    ->label('Icon Source')
                    ->options([
                        'predefined' => 'Predefined Icon',
                        'custom' => 'Custom Upload',
                    ])
                    ->inline()
                    ->live()
                    ->afterStateHydrated(function (Get $get, Set $set) {
                        $iconName = $get('icon_name');
                        if ($iconName === 'custom') {
                            $set('icon_source', 'custom');
                        } else {
                            $set('icon_source', 'predefined');
                        }
                    })
                    ->afterStateUpdated(function ($state, Set $set) {
                        if ($state === 'custom') {
                            $set('icon_name', 'custom');
                        } else {
                            $set('icon_name', null);
                        }
                    }),

                ViewField::make('icon_name')
                    ->label('Icon')
                    ->view('filament.forms.components.box-icon-picker')
                    ->viewData([
                        'icons' => array_map(
                            fn ($file) => $file->getFilename(),
                            array_filter(
                                File::files(public_path('icons')),
                                fn ($file) => str_ends_with($file->getFilename(), '.svg')
                            )
                        ),
                    ])
                    ->required()
                    ->visible(fn (Get $get) => $get('icon_source') === 'predefined')
                    ->dehydrated(true),

                SpatieMediaLibraryFileUpload::make('image')
                    ->label('Custom Icon Image')
                    ->collection('box-item-custom-icon')
                    ->image()
                    ->imageEditor()
                    ->imagePreviewHeight(250)
                    ->visible(fn (Get $get) => $get('icon_source') === 'custom')
                    ->required(fn (Get $get) => $get('icon_source') === 'custom'),
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
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (($data['icon_source'] ?? null) === 'custom') {
                            $data['icon_name'] = 'custom';
                        }

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (($data['icon_source'] ?? null) === 'custom') {
                            $data['icon_name'] = 'custom';
                        }

                        return $data;
                    })
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
