<?php

namespace App\Filament\Resources\ResellerBanners\Pages;

use App\Filament\Resources\ResellerBanners\ResellerBannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResellerBanners extends ListRecords
{
    protected static string $resource = ResellerBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
