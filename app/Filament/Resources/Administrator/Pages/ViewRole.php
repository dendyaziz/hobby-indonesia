<?php

namespace App\Filament\Resources\Administrator\Pages;

use App\Filament\Resources\Administrator\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRole extends ViewRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->hidden(fn ($record) => $record->name === 'Super Admin'),
        ];
    }
}
