<?php

namespace App\Filament\Resources\Administrator\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        $groups = [
            'Public' => ['Article', 'Testimony', 'Faq', 'Partner', 'SocialMedia', 'Contact'],
            'Listing' => ['Product', 'Category', 'Collection'],
            'Homepage' => ['ProductBanner'],
            'Reseller' => ['Event', 'ResellerBanner'],
            'Administrator' => ['User', 'Role'],
        ];

        $permissionSections = [];

        foreach ($groups as $groupName => $models) {
            $permissionNames = collect($models)->flatMap(fn ($m) => ["view {$m}", "manage {$m}"])->toArray();

            $permissionSections[] = Section::make($groupName)
                ->schema([
                    CheckboxList::make("permissions_{$groupName}")
                        ->options(function () use ($permissionNames) {
                            return Permission::whereIn('name', $permissionNames)
                                ->get()
                                ->sortBy(function ($permission) {
                                    $isView = str_starts_with($permission->name, 'view') ? 1 : 0;
                                    $modelName = explode(' ', $permission->name)[1] ?? '';

                                    return $isView.'_'.$modelName;
                                })
                                ->mapWithKeys(function ($permission) {
                                    $label = Str::headline(str_replace(' ', '_', $permission->name));
                                    $label = str_replace('Faq', 'QnA', $label);

                                    return [$permission->name => $label];
                                })->toArray();
                        })
                        ->columns(2)
                        ->hiddenLabel()
                        ->loadStateFromRelationshipsUsing(function ($component, $record) use ($permissionNames) {
                            if ($record) {
                                if ($record->name === 'Super Admin') {
                                    $component->state($permissionNames);
                                } else {
                                    $component->state($record->permissions->whereIn('name', $permissionNames)->pluck('name')->toArray());
                                }
                            }
                        })
                        ->saveRelationshipsUsing(function ($record, $state) use ($permissionNames) {
                            $current = $record->permissions->pluck('name')->toArray();
                            $current = array_diff($current, $permissionNames); // Remove this group's permissions
                            $new = array_merge($current, $state ?? []); // Add selected ones
                            $record->syncPermissions($new);
                        })
                        ->dehydrated(false),
                ])
                ->compact();
        }

        return $schema
            ->columns(1)
            ->components([
                Section::make('Role Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ]),

                Section::make()
                    ->schema($permissionSections),
            ]);
    }
}
