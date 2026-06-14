<?php

namespace App\Filament\Resources\Partners\Schemas;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;

class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(30),
                                SpatieMediaLibraryFileUpload::make('image')
                                    ->collection('partners')
                                    ->image()
                                    ->imageEditor()
                                    ->imagePreviewHeight(250)
                                    ->required(),
                            ]),
                      ])
                      ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Visibility')
                            ->schema([
                                Radio::make('status')
                                    ->label('Status')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                    ])
                                    ->required()
                                    ->default('active'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ]);
    }
}
