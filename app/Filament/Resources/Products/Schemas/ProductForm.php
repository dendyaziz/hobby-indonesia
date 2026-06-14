<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Category;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(100)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                                TextInput::make('slug')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Product::class, 'slug', ignoreRecord: true),
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
                            ])
                            ->columns(2),

                        Section::make('Images')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('images')
                                    ->collection('product-images')
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->imagePreviewHeight(250)
                                    ->required(),
                            ])
                            ->collapsible(),

                        Section::make('Pricing')
                            ->schema([
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
                            ])
                            ->columns(3),

                        Section::make('Attributes')
                            ->schema([
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
                                TextInput::make('playing_duration')
                                    ->numeric()
                                    ->minValue(1)
                                    ->suffix('minutes'),
                                TextInput::make('min_player')
                                    ->label('Minimum player')
                                    ->numeric()
                                    ->minValue(1),
                                TextInput::make('max_player')
                                    ->label('Maximum player')
                                    ->numeric()
                                    ->minValue(1),
                            ])
                            ->columns(2),

                        Section::make('Video Attachment')
                            ->schema([
                                TextInput::make('youtube')
                                    ->label('YouTube Video URL')
                                    ->maxLength(100)
                                    ->helperText('Input a valid YouTube video URL (e.g., https://www.youtube.com/watch?v=...)')
                                    ->rules([
                                        fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                            $value = trim($value);
                                            if (empty($value)) return;

                                            // Matches standard watch URLs (with v parameter anywhere in query string),
                                            // embed/v/shorts paths, and short youtu.be links.
                                            $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?(.*&)?v=|embed\/|v\/|shorts\/)|youtu\.be\/)[a-zA-Z0-9_\-]{11}([?&].*)?$/i';

                                            if (!preg_match($pattern, $value)) {
                                                $fail('The YouTube video URL is not valid.');
                                            }
                                        }
                                    ]),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Availability')
                            ->schema([
                                Radio::make('availability')
                                    ->label('Status')
                                    ->options([
                                        'Available' => 'Available',
                                        'Out of stock' => 'Out of stock',
                                        'Pre-order' => 'Pre-order',
                                    ])
                                    ->required(),
                            ]),

                        Section::make('Associations')
                            ->schema([
                                Select::make('categories')
                                    ->relationship(
                                        name: 'categories',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn (Builder $query) => $query->with('parent')->sub()->latest(),
                                    )
                                    ->getOptionLabelFromRecordUsing(fn (Category $record) => "{$record->parent?->name} → {$record->name}")
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->label('Categories'),
                                SpatieTagsInput::make('tags')
                                    ->placeholder('Add tags...'),
                                TextInput::make('brand')
                                    ->maxLength(50),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
