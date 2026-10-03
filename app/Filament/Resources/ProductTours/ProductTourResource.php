<?php

namespace App\Filament\Resources\ProductTours;

use App\Filament\Resources\ProductTours\Pages\CreateProductTour;
use App\Filament\Resources\ProductTours\Pages\EditProductTour;
use App\Filament\Resources\ProductTours\Pages\ListProductTours;
use App\Filament\Resources\ProductTours\RelationManagers\StepsRelationManager;
use App\Filament\Resources\ProductTours\Schemas\ProductTourForm;
use App\Filament\Resources\ProductTours\Tables\ProductToursTable;
use App\Models\ProductTour;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ProductTourResource extends Resource
{
    protected static ?string $model = ProductTour::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static string|UnitEnum|null $navigationGroup = 'Support';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'key';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Support');
    }

    public static function getNavigationLabel(): string
    {
        return __('Product tours');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductTourForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductToursTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [StepsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductTours::route('/'),
            'create' => CreateProductTour::route('/create'),
            'edit' => EditProductTour::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return true;
    }
}
