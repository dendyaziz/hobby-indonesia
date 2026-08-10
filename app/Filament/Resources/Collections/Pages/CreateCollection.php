<?php

namespace App\Filament\Resources\Collections\Pages;

use App\Filament\Resources\Banners\Traits\HasGroupBreadcrumbs;
use App\Filament\Resources\Collections\CollectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCollection extends CreateRecord
{
    use HasGroupBreadcrumbs;

    protected static string $resource = CollectionResource::class;
}
