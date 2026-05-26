<?php

namespace App\Filament\Resources\Testimonies;

use App\Filament\Resources\Testimonies\Pages\CreateTestimony;
use App\Filament\Resources\Testimonies\Pages\EditTestimony;
use App\Filament\Resources\Testimonies\Pages\ListTestimonies;
use App\Filament\Resources\Testimonies\Schemas\TestimonyForm;
use App\Filament\Resources\Testimonies\Tables\TestimoniesTable;
use App\Models\Testimony;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TestimonyResource extends Resource
{
    protected static ?string $model = Testimony::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TestimonyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TestimoniesTable::configure($table);
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
            'index' => ListTestimonies::route('/'),
            'create' => CreateTestimony::route('/create'),
            'edit' => EditTestimony::route('/{record}/edit'),
        ];
    }
}
