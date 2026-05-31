<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(100),
                FileUpload::make('image')
                    ->image()
                    ->imageEditor()
                    ->directory('events'),
                Select::make('partners')
                    ->relationship('partners', 'name')
                    ->multiple()
                    ->searchable(),
                RichEditor::make('description')
                    ->required()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'strike',
                        'link',
                        'bulletList',
                        'orderedList',
                        'undo',
                        'redo',
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
