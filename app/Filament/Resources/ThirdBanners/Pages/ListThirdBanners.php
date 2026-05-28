<?php

namespace App\Filament\Resources\ThirdBanners\Pages;

use App\Filament\Resources\ThirdBanners\ThirdBannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListThirdBanners extends ListRecords
{
    protected static string $resource = ThirdBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
