<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(100),
                Radio::make('availability')
                    ->options([
                        'Available' => 'Available',
                        'Out of stock' => 'Out of stock',
                        'Pre-order' => 'Pre-order',
                    ])
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('discount_percentage')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01)
                    ->suffix('%'),
                TextInput::make('discounted_price')
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('brand')
                    ->maxLength(50),
                TextInput::make('manufacture_country')
                    ->maxLength(50),
                TextInput::make('publisher')
                    ->maxLength(50),
                TextInput::make('designer')
                    ->maxLength(50),
                TextInput::make('artist')
                    ->maxLength(50),
                TextInput::make('min_age')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(50),
                TextInput::make('min_player')
                    ->numeric()
                    ->minValue(1),
                TextInput::make('max_player')
                    ->numeric()
                    ->minValue(1),
                TextInput::make('playing_duration')
                    ->numeric()
                    ->minValue(1),
                TextInput::make('youtube')
                    ->maxLength(100)
                    ->helperText('Input channel ID or URL')
                    ->rules([
                        fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                            $value = trim($value);
                            if (empty($value)) return;
                            if (filter_var($value, FILTER_VALIDATE_URL) || str_contains($value, 'youtube.com') || str_contains($value, 'youtu.be')) {
                                if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/[a-zA-Z0-9.@%\/\-\?=&_]+$/i', $value)) {
                                    $fail('The YouTube URL is not valid.');
                                }
                            } else {
                                if (str_starts_with($value, '@')) {
                                    if (!preg_match('/^@[a-zA-Z0-9._\-]{3,30}$/', $value)) {
                                        $fail('The YouTube handle must start with @ and be 3 to 30 characters long.');
                                    }
                                } else {
                                    if (!preg_match('/^[a-zA-Z0-9._\-]{3,50}$/', $value)) {
                                        $fail('The YouTube channel ID or username is not valid.');
                                    }
                                }
                            }
                        }
                    ]),
                RichEditor::make('description')
                    ->required()
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'link', 'attachFiles'],
                        [ToolbarButtonGroup::make('Paragraph', ['paragraph', 'h2', 'h3'])],
                        [ToolbarButtonGroup::make('Alignment', ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'])],
                        ['blockquote', 'bulletList', 'orderedList'],
                        ['undo', 'redo'],
                    ])
                    ->fileAttachmentsDirectory('public/products/attachments')
                    ->fileAttachmentsMaxSize(2048)
                    ->resizableImages()
                    ->preventFileAttachmentPathTampering()
                    ->columnSpanFull(),
            ]);
    }
}
