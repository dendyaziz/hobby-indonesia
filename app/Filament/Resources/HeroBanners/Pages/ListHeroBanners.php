<?php

namespace App\Filament\Resources\HeroBanners\Pages;

use App\Filament\Resources\Banners\Traits\HasGroupBreadcrumbs;
use App\Filament\Resources\HeroBanners\HeroBannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHeroBanners extends ListRecords
{
    use HasGroupBreadcrumbs;
    protected static string $resource = HeroBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
