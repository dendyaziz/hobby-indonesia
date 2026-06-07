<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                    ->prefix('Rp')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        if (blank($state)) {
                            $set('discount_percentage', null);
                            $set('discounted_price', null);
                            return;
                        }

                        $discountPercentage = $get('discount_percentage');
                        $discountedPrice = $get('discounted_price');

                        if (filled($discountPercentage)) {
                            $set('discounted_price', (int) round($state * (1 - $discountPercentage / 100)));
                        } elseif (filled($discountedPrice)) {
                            $set('discount_percentage', round((($state - $discountedPrice) / $state) * 100, 2));
                        }
                    }),
                TextInput::make('discount_percentage')
                    ->label('Discount')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01)
                    ->suffix('%')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $price = $get('price');
                        if (blank($price) || blank($state)) {
                            $set('discounted_price', null);
                            return;
                        }
                        $set('discounted_price', (int) round($price * (1 - $state / 100)));
                    }),
                TextInput::make('discounted_price')
                    ->numeric()
                    ->prefix('Rp')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $price = $get('price');
                        if (blank($price) || blank($state)) {
                            $set('discount_percentage', null);
                            return;
                        }
                        $set('discount_percentage', round((($price - $state) / $price) * 100, 2));
                    }),
                TextInput::make('brand')
                    ->maxLength(50),
                TextInput::make('manufacture_country')
                    ->label('Country of manufacture')
                    ->maxLength(50),
                TextInput::make('publisher')
                    ->maxLength(50),
                TextInput::make('designer')
                    ->maxLength(50),
                TextInput::make('artist')
                    ->maxLength(50),
                TextInput::make('min_age')
                    ->label('Minimum age')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(50),
                TextInput::make('min_player')
                    ->label('Minimum player')
                    ->numeric()
                    ->minValue(1),
                TextInput::make('max_player')
                    ->label('Maximum player')
                    ->numeric()
                    ->minValue(1),
                TextInput::make('playing_duration')
                    ->numeric()
                    ->minValue(1)
                    ->suffix('minutes'),
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
