<?php

namespace App\Filament\Resources\ProductBanners\Pages;

use App\Filament\Resources\Banners\Traits\HasGroupBreadcrumbs;
use App\Filament\Resources\ProductBanners\ProductBannerResource;
use Filament\Resources\Pages\EditRecord;

class EditProductBanner extends EditRecord
{
    use HasGroupBreadcrumbs;

    protected static string $resource = ProductBannerResource::class;
}
