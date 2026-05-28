<?php

namespace App\Filament\Resources\ProductBanners\Pages;

use App\Filament\Resources\ProductBanners\ProductBannerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductBanner extends CreateRecord
{
    protected static string $resource = ProductBannerResource::class;
}
