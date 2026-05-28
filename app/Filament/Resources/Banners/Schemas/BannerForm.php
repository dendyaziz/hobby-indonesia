<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;

class BannerForm
{
    public static function configure(Schema $schema, string $placement): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Hidden::make('placement')
                    ->default($placement),

                TextInput::make('title')
                    ->required()
                    ->maxLength(100),

                FileUpload::make('image')
                    ->image()
                    ->imageEditor()
                    ->directory('banners')
                    ->required(),

                Radio::make('type')
                    ->options([
                        'none' => 'None',
                        'link' => 'Link',
                    ])
                    ->required()
                    ->default('none')
                    ->live(),

                TextInput::make('url')
                    ->url()
                    ->maxLength(255)
                    ->visible(fn (Get $get) => $get('type') === 'link')
                    ->required(fn (Get $get) => $get('type') === 'link'),

                DatePicker::make('start_date')
                    ->native(false)
                    ->nullable()
                    ->live(),

                DatePicker::make('end_date')
                    ->native(false)
                    ->required(fn (Get $get) => filled($get('start_date')))
                    ->afterOrEqual('start_date'),

                Radio::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->required()
                    ->default('active'),
            ]);
    }
}
