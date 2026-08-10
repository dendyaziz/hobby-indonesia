<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(100),
                        SpatieMediaLibraryFileUpload::make('image')
                            ->collection('events')
                            ->image()
                            ->imageEditor()
                            ->imagePreviewHeight(250),
                        Select::make('partners')
                            ->relationship(
                                name: 'partners',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query->latest(),
                            )
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        RichEditor::make('description')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'link',
                                'bulletList',
                                'orderedList',
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
