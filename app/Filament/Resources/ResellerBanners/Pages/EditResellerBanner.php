<?php

namespace App\Filament\Resources\ResellerBanners\Pages;

use App\Filament\Resources\Banners\Traits\HasGroupBreadcrumbs;
use App\Filament\Resources\ResellerBanners\ResellerBannerResource;
use Filament\Resources\Pages\EditRecord;

class EditResellerBanner extends EditRecord
{
    use HasGroupBreadcrumbs;

    protected static string $resource = ResellerBannerResource::class;
}
