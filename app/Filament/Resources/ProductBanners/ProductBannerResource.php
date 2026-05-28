<?php

namespace App\Filament\Resources\ProductBanners;

use App\Filament\Resources\Banners\Schemas\BannerForm;
use App\Filament\Resources\Banners\Tables\BannersTable;
use App\Models\Banner;
use App\Filament\Resources\ProductBanners\Pages\CreateProductBanner;
use App\Filament\Resources\ProductBanners\Pages\EditProductBanner;
use App\Filament\Resources\ProductBanners\Pages\ListProductBanners;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProductBannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Homepage';

    protected static ?string $navigationLabel = 'Product Banner';

    protected static ?string $modelLabel = 'Product Banner';

    protected static ?string $pluralModelLabel = 'Product Banners';

    protected static ?string $slug = 'homepage/product-banners';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('placement', 'product-page--top');
    }

    public static function form(Schema $schema): Schema
    {
        return BannerForm::configure($schema, 'product-page--top');
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
            'index' => ListProductBanners::route('/'),
            'create' => CreateProductBanner::route('/create'),
            'edit' => EditProductBanner::route('/{record}/edit'),
        ];
    }
}
