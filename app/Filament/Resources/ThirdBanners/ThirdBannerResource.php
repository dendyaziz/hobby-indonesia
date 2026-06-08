<?php

namespace App\Filament\Resources\ThirdBanners;

use App\Filament\Resources\Banners\Schemas\BannerForm;
use App\Filament\Resources\Banners\Tables\BannersTable;
use App\Models\Banner;
use App\Filament\Resources\ThirdBanners\Pages\CreateThirdBanner;
use App\Filament\Resources\ThirdBanners\Pages\EditThirdBanner;
use App\Filament\Resources\ThirdBanners\Pages\ListThirdBanners;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ThirdBannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Homepage';

    protected static ?string $navigationLabel = 'Third Banner';

    protected static ?string $modelLabel = 'Third Banner';

    protected static ?string $pluralModelLabel = 'Third Banners';

    protected static ?string $slug = 'homepage/third-banners';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('placement', 'homepage--third');
    }

    public static function form(Schema $schema): Schema
    {
        return BannerForm::configure($schema, 'homepage--third');
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
            'index' => ListThirdBanners::route('/'),
            'create' => CreateThirdBanner::route('/create'),
            'edit' => EditThirdBanner::route('/{record}/edit'),
        ];
    }
}
