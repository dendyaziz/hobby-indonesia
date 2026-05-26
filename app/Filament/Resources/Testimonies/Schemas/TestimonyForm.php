<?php

namespace App\Filament\Resources\Testimonies\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TestimonyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(30),
                MarkdownEditor::make('testimony')
                    ->required()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'strike',
                        'undo',
                        'redo',
                    ])
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->image()
                    ->disk('s3')
                    ->directory('testimonies'),
            ]);
    }
}
