<?php

namespace App\Filament\Resources\ThirdBanners\Pages;

use App\Filament\Resources\Banners\Traits\HasGroupBreadcrumbs;
use App\Filament\Resources\ThirdBanners\ThirdBannerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateThirdBanner extends CreateRecord
{
    use HasGroupBreadcrumbs;
    protected static string $resource = ThirdBannerResource::class;
}
