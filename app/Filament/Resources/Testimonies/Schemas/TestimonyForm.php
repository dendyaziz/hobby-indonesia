<?php

namespace App\Filament\Resources\Testimonies\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class TestimonyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Person Name')
                            ->required()
                            ->maxLength(30),
                        TextInput::make('subtitle')
                            ->label('Brand Name')
                            ->required()
                            ->maxLength(50),
                        RichEditor::make('testimony')
                            ->required()
                            ->toolbarButtons([
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull(),
                        SpatieMediaLibraryFileUpload::make('image')
                            ->collection('testimonies')
                            ->image()
                            ->imageEditor()
                            ->imagePreviewHeight(250),
                    ]),
            ]);
    }
}
