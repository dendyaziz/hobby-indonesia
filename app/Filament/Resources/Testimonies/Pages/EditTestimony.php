<?php

namespace App\Filament\Resources\Testimonies\Pages;

use App\Filament\Resources\Testimonies\TestimonyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTestimony extends EditRecord
{
    protected static string $resource = TestimonyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
