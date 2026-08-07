<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\RelationManagers\BoxItemsRelationManager;
use App\Filament\Resources\Products\RelationManagers\CharactersRelationManager;
use App\Filament\Resources\Products\RelationManagers\GuidesRelationManager;
use App\Models\Product;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
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
                                Select::make('difficulty')
                                    ->options([
                                        'Easy' => 'Easy',
                                        'Medium' => 'Medium',
                                        'Hard' => 'Hard',
                                    ])
                                    ->required(),
                                Select::make('themes')
                                    ->label('Theme')
                                    ->options([
                                        'Abstract' => 'Abstract',
                                        'Adventure' => 'Adventure',
                                        'Animals' => 'Animals',
                                        'City Building' => 'City Building',
                                        'Civilization' => 'Civilization',
                                        'Cooperative' => 'Cooperative',
                                        'Deduction / Mystery' => 'Deduction / Mystery',
                                        'Dungeon Crawler' => 'Dungeon Crawler',
                                        'Economy' => 'Economy',
                                        'Fantasy' => 'Fantasy',
                                        'Horror' => 'Horror',
                                        'Racing' => 'Racing',
                                        'Sci-Fi' => 'Sci-Fi',
                                        'Survival' => 'Survival',
                                        'War / Historical' => 'War / Historical',
                                    ])
                                    ->multiple()
                                    ->required(),
                            ])
                            ->columns(2),

                        Section::make('Everything you need to know')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('everything_you_need_to_know_image')
                                    ->label('Image')
                                    ->collection('everything-you-need-to-know-image')
                                    ->image()
                                    ->imageEditor()
                                    ->imagePreviewHeight(250)
                                    ->columnSpanFull(),
                                RichEditor::make('everything_you_need_to_know_description')
                                    ->label('Description')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                    ])
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Playbook & Video Attachments')
                            ->schema([
                                TextInput::make('playbook_url')
                                    ->label('Playbook Link')
                                    ->url()
                                    ->placeholder('https://drive.google.com/file/d/.../view?usp=sharing')
                                    ->helperText('Input a valid URL for the playbook (usually a Google Drive link). If left empty, the playbook section will be hidden on the frontend.')
                                    ->nullable()
                                    ->columnSpanFull(),
                                TextInput::make('youtube')
                                    ->label('YouTube Video URL')
                                    ->maxLength(100)
                                    ->helperText('Input a valid YouTube video URL (e.g., https://www.youtube.com/watch?v=...)')
                                    ->rules([
                                        fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                            $value = trim($value);
                                            if (empty($value)) {
                                                return;
                                            }

                                            // Matches standard watch URLs (with v parameter anywhere in query string),
                                            // embed/v/shorts paths, and short youtu.be links.
                                            $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?(.*&)?v=|embed\/|v\/|shorts\/)|youtu\.be\/)[a-zA-Z0-9_\-]{11}([?&].*)?$/i';

                                            if (! preg_match($pattern, $value)) {
                                                $fail('The YouTube video URL is not valid.');
                                            }
                                        },
                                    ]),
                                Repeater::make('tiktok_videos')
                                    ->label('TikTok Video URLs')
                                    ->simple(
                                        TextInput::make('url')
                                            ->label('TikTok Video URL')
                                            ->placeholder('https://www.tiktok.com/@username/video/...')
                                            ->rules([
                                                fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                                    $value = trim($value);
                                                    if (empty($value)) {
                                                        return;
                                                    }

                                                    $pattern = '/^(https?:\/\/)?((www|vm|vt|t|m)\.)?tiktok\.com\/[a-zA-Z0-9_@\.\-\/]+([?&].*)?$/i';

                                                    if (! preg_match($pattern, $value)) {
                                                        $fail('The TikTok video URL is not valid.');
                                                    }
                                                },
                                            ])
                                    )
                                    ->addActionLabel('Add TikTok Video URL')
                                    ->default([])
                                    ->nullable(),
                            ]),
                        Livewire::make(CharactersRelationManager::class, fn (?Product $record) => $record ? [
                            'ownerRecord' => $record,
                            'pageClass' => EditProduct::class,
                        ] : [])
                            ->key('characters-relation-manager')
                            ->visible(fn (?Product $record) => $record !== null),
                        Livewire::make(GuidesRelationManager::class, fn (?Product $record) => $record ? [
                            'ownerRecord' => $record,
                            'pageClass' => EditProduct::class,
                        ] : [])
                            ->key('guides-relation-manager')
                            ->visible(fn (?Product $record) => $record !== null),
                        Livewire::make(BoxItemsRelationManager::class, fn (?Product $record) => $record ? [
                            'ownerRecord' => $record,
                            'pageClass' => EditProduct::class,
                        ] : [])
                            ->key('box-items-relation-manager')
                            ->visible(fn (?Product $record) => $record !== null),
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
                                        modifyQueryUsing: fn (Builder $query) => $query->latest(),
                                    )
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
