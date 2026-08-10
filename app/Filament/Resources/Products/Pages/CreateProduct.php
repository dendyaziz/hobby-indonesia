<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Banners\Traits\HasGroupBreadcrumbs;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use HasGroupBreadcrumbs;

    protected static string $resource = ProductResource::class;
}
