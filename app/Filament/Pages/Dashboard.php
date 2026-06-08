<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static bool $shouldRegisterNavigation = false;

    public function mount(): void
    {
        redirect()->to(ArticleResource::getUrl('index'));
    }
}
