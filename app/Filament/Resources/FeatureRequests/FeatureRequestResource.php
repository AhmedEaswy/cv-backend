<?php

namespace App\Filament\Resources\FeatureRequests;

use App\Filament\Resources\FeatureRequests\Pages\EditFeatureRequest;
use App\Filament\Resources\FeatureRequests\Pages\ListFeatureRequests;
use App\Filament\Resources\FeatureRequests\Schemas\FeatureRequestForm;
use App\Filament\Resources\FeatureRequests\Tables\FeatureRequestsTable;
use App\Models\FeatureRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class FeatureRequestResource extends Resource
{
    protected static ?string $model = FeatureRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-light-bulb';

    protected static string|UnitEnum|null $navigationGroup = 'Support';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Support');
    }

    public static function getNavigationLabel(): string
    {
        return __('Feature requests');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return FeatureRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeatureRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeatureRequests::route('/'),
            'edit' => EditFeatureRequest::route('/{record}/edit'),
        ];
    }
}
