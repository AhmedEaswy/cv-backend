<?php

namespace App\Filament\Resources\CoverLetters;

use App\Filament\Resources\CoverLetters\Pages\CreateCoverLetter;
use App\Filament\Resources\CoverLetters\Pages\EditCoverLetter;
use App\Filament\Resources\CoverLetters\Pages\ListCoverLetters;
use App\Filament\Resources\CoverLetters\Pages\ViewCoverLetter;
use App\Filament\Resources\CoverLetters\Schemas\CoverLetterForm;
use App\Filament\Resources\CoverLetters\Schemas\CoverLetterInfolist;
use App\Filament\Resources\CoverLetters\Tables\CoverLettersTable;
use App\Models\CoverLetter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CoverLetterResource extends Resource
{
    protected static ?string $model = CoverLetter::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|UnitEnum|null $navigationGroup = 'Cover Letters';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Cover Letters');
    }

    public static function getNavigationLabel(): string
    {
        return __('cover_letters');
    }

    public static function getModelLabel(): string
    {
        return __('cover_letters');
    }

    public static function getPluralModelLabel(): string
    {
        return __('cover_letters');
    }

    public static function form(Schema $schema): Schema
    {
        return CoverLetterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CoverLetterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoverLettersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoverLetters::route('/'),
            'create' => CreateCoverLetter::route('/create'),
            'view' => ViewCoverLetter::route('/{record}'),
            'edit' => EditCoverLetter::route('/{record}/edit'),
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
