<?php

namespace App\Filament\Resources\PublicProfiles;

use App\Filament\Resources\PublicProfiles\Pages\CreatePublicProfile;
use App\Filament\Resources\PublicProfiles\Pages\EditPublicProfile;
use App\Filament\Resources\PublicProfiles\Pages\ListPublicProfiles;
use App\Filament\Resources\PublicProfiles\Pages\ViewPublicProfile;
use App\Filament\Resources\PublicProfiles\Schemas\PublicProfileForm;
use App\Filament\Resources\PublicProfiles\Schemas\PublicProfileInfolist;
use App\Filament\Resources\PublicProfiles\Tables\PublicProfilesTable;
use App\Models\PublicProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PublicProfileResource extends Resource
{
    protected static ?string $model = PublicProfile::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static string|UnitEnum|null $navigationGroup = 'Public Profiles';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'slug';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Public Profiles');
    }

    public static function getNavigationLabel(): string
    {
        return __('Public Profiles');
    }

    public static function getModelLabel(): string
    {
        return __('Public Profile');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Public Profiles');
    }

    public static function form(Schema $schema): Schema
    {
        return PublicProfileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PublicProfileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PublicProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublicProfiles::route('/'),
            'create' => CreatePublicProfile::route('/create'),
            'view' => ViewPublicProfile::route('/{record}'),
            'edit' => EditPublicProfile::route('/{record}/edit'),
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
