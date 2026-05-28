<?php

namespace App\Filament\Resources\HeroBanners\Pages;

use App\Filament\Resources\Banners\Traits\HasGroupBreadcrumbs;
use App\Filament\Resources\HeroBanners\HeroBannerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHeroBanner extends CreateRecord
{
    use HasGroupBreadcrumbs;
    protected static string $resource = HeroBannerResource::class;
}
