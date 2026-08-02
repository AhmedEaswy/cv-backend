<?php

namespace App\Filament\Resources\AtsChecks;

use App\Filament\Resources\AtsChecks\Pages\ListAtsChecks;
use App\Filament\Resources\AtsChecks\Pages\ViewAtsCheck;
use App\Filament\Resources\AtsChecks\Schemas\AtsCheckInfolist;
use App\Filament\Resources\AtsChecks\Tables\AtsChecksTable;
use App\Models\AtsCheck;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AtsCheckResource extends Resource
{
    protected static ?string $model = AtsCheck::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|UnitEnum|null $navigationGroup = 'CV Builder';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'candidate_name';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('CV Builder');
    }

    public static function getNavigationLabel(): string
    {
        return __('ATS Checks');
    }

    public static function getModelLabel(): string
    {
        return __('ATS Check');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ATS Checks');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function infolist(Schema $schema): Schema
    {
        return AtsCheckInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtsChecksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAtsChecks::route('/'),
            'view' => ViewAtsCheck::route('/{record}'),
        ];
    }
}
