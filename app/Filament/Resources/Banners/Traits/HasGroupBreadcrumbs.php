<?php

namespace App\Filament\Resources\Banners\Traits;

use UnitEnum;

trait HasGroupBreadcrumbs
{
    public function getBreadcrumbs(): array
    {
        $groupName = static::getResource()::getNavigationGroup();

        if ($groupName instanceof UnitEnum) {
            $groupName = $groupName->name;
        }

        return array_merge(
            ['#' => $groupName],
            $this->getResourceBreadcrumbs(),
            [$this->getBreadcrumb()]
        );
    }
}
