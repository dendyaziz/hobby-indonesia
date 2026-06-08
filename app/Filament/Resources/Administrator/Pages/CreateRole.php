<?php

namespace App\Filament\Resources\Administrator\Pages;

use App\Filament\Resources\Administrator\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
}
