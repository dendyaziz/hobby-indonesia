<?php

namespace App\Filament\Resources\ProductBanners\Pages;

use App\Filament\Resources\Banners\Traits\HasGroupBreadcrumbs;
use App\Filament\Resources\ProductBanners\ProductBannerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductBanner extends CreateRecord
{
    use HasGroupBreadcrumbs;
    protected static string $resource = ProductBannerResource::class;
}
