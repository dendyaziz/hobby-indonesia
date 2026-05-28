<?php

namespace App\Filament\Resources\ResellerBanners;

use App\Filament\Resources\Banners\Schemas\BannerForm;
use App\Filament\Resources\Banners\Tables\BannersTable;
use App\Models\Banner;
use App\Filament\Resources\ResellerBanners\Pages\CreateResellerBanner;
use App\Filament\Resources\ResellerBanners\Pages\EditResellerBanner;
use App\Filament\Resources\ResellerBanners\Pages\ListResellerBanners;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ResellerBannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'Reseller';

    protected static ?string $navigationLabel = 'Banner';

    protected static ?string $modelLabel = 'Banner';

    protected static ?string $pluralModelLabel = 'Banners';

    protected static ?string $slug = 'reseller/banners';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('placement', 'reseller-page--top');
    }

    public static function form(Schema $schema): Schema
    {
        return BannerForm::configure($schema, 'reseller-page--top');
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
            'index' => ListResellerBanners::route('/'),
            'create' => CreateResellerBanner::route('/create'),
            'edit' => EditResellerBanner::route('/{record}/edit'),
        ];
    }
}
