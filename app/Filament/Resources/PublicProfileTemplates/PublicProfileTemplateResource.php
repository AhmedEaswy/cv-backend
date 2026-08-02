<?php

namespace App\Filament\Resources\PublicProfileTemplates;

use App\Filament\Resources\PublicProfileTemplates\Pages\CreatePublicProfileTemplate;
use App\Filament\Resources\PublicProfileTemplates\Pages\EditPublicProfileTemplate;
use App\Filament\Resources\PublicProfileTemplates\Pages\ListPublicProfileTemplates;
use App\Filament\Resources\PublicProfileTemplates\Pages\ViewPublicProfileTemplate;
use App\Filament\Resources\PublicProfileTemplates\Schemas\PublicProfileTemplateForm;
use App\Filament\Resources\PublicProfileTemplates\Schemas\PublicProfileTemplateInfolist;
use App\Filament\Resources\PublicProfileTemplates\Tables\PublicProfileTemplatesTable;
use App\Models\PublicProfileTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PublicProfileTemplateResource extends Resource
{
    protected static ?string $model = PublicProfileTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|UnitEnum|null $navigationGroup = 'Public Profiles';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Public Profiles');
    }

    public static function getNavigationLabel(): string
    {
        return __('Public Profile Templates');
    }

    public static function getModelLabel(): string
    {
        return __('Public Profile Template');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Public Profile Templates');
    }

    public static function form(Schema $schema): Schema
    {
        return PublicProfileTemplateForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PublicProfileTemplateInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PublicProfileTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PublicProfilesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublicProfileTemplates::route('/'),
            'create' => CreatePublicProfileTemplate::route('/create'),
            'view' => ViewPublicProfileTemplate::route('/{record}'),
            'edit' => EditPublicProfileTemplate::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
