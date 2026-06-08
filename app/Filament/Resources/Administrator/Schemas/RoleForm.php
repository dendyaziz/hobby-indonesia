<?php

namespace App\Filament\Resources\Administrator\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Role Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ]),

                Section::make('Permissions')
                    ->description('Select the permissions assigned to this role.')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->relationship(
                                name: 'permissions',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->orderBy('name')
                            )
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                $label = \Illuminate\Support\Str::headline(str_replace(' ', '_', $record->name));
                                return str_replace('Faq', 'QnA', $label);
                            })
                            ->columns(2)
                            ->label('Permissions'),
                    ]),
            ]);
    }
}
