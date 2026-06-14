<?php

namespace App\Filament\Resources\Administrator;

use Spatie\Permission\Models\Role;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\Administrator\Schemas\RoleForm;
use App\Filament\Resources\Administrator\Tables\RolesTable;
use Illuminate\Database\Eloquent\Model;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Administrator';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'administrator/roles';

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function canEdit(Model $record): bool
    {
        return $record->name !== 'Super Admin';
    }

    public static function canDelete(Model $record): bool
    {
        return $record->name !== 'Super Admin';
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
