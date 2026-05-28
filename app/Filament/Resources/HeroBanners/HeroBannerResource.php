<?php

namespace App\Filament\Resources\HeroBanners;

use App\Filament\Resources\Banners\Schemas\BannerForm;
use App\Filament\Resources\Banners\Tables\BannersTable;
use App\Filament\Resources\HeroBanners\Pages\CreateHeroBanner;
use App\Filament\Resources\HeroBanners\Pages\EditHeroBanner;
use App\Filament\Resources\HeroBanners\Pages\ListHeroBanners;
use App\Models\Banner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class HeroBannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'Homepage';

    protected static ?string $navigationLabel = 'Hero Banner';

    protected static ?string $modelLabel = 'Hero Banner';

    protected static ?string $pluralModelLabel = 'Hero Banners';

    protected static ?string $slug = 'homepage/hero-banners';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('placement', 'homepage--hero');
    }

    public static function form(Schema $schema): Schema
    {
        return BannerForm::configure($schema, 'homepage--hero');
    }

    public static function table(Table $table): Table
    {
        return BannersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHeroBanners::route('/'),
            'create' => CreateHeroBanner::route('/create'),
            'edit' => EditHeroBanner::route('/{record}/edit'),
        ];
    }
}
