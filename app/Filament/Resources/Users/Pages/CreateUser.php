<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Banners\Traits\HasGroupBreadcrumbs;
use App\Filament\Resources\Users\UserResource;
use App\Notifications\AdminPasswordSetupNotification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use HasGroupBreadcrumbs;

    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $this->record->notify(new AdminPasswordSetupNotification);
    }
}
