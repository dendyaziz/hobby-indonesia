<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Banners\Traits\HasGroupBreadcrumbs;
use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    use HasGroupBreadcrumbs;

    protected static string $resource = CategoryResource::class;
}
