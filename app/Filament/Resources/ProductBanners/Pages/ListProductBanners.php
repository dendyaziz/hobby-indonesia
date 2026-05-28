<?php

namespace App\Filament\Resources\ProductBanners\Pages;

use App\Filament\Resources\Banners\Traits\HasGroupBreadcrumbs;
use App\Filament\Resources\ProductBanners\ProductBannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductBanners extends ListRecords
{
    use HasGroupBreadcrumbs;
    protected static string $resource = ProductBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
