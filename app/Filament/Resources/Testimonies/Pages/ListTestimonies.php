<?php

namespace App\Filament\Resources\Testimonies\Pages;

use App\Filament\Resources\Testimonies\TestimonyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTestimonies extends ListRecords
{
    protected static string $resource = TestimonyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
