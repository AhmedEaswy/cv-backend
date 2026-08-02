<?php

namespace App\Filament\Resources\CoverLetterTemplates;

use App\Filament\Resources\CoverLetterTemplates\Pages\CreateCoverLetterTemplate;
use App\Filament\Resources\CoverLetterTemplates\Pages\EditCoverLetterTemplate;
use App\Filament\Resources\CoverLetterTemplates\Pages\ListCoverLetterTemplates;
use App\Filament\Resources\CoverLetterTemplates\Pages\ViewCoverLetterTemplate;
use App\Filament\Resources\CoverLetterTemplates\Schemas\CoverLetterTemplateForm;
use App\Filament\Resources\CoverLetterTemplates\Schemas\CoverLetterTemplateInfolist;
use App\Filament\Resources\CoverLetterTemplates\Tables\CoverLetterTemplatesTable;
use App\Models\CoverLetterTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CoverLetterTemplateResource extends Resource
{
    protected static ?string $model = CoverLetterTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Cover Letters';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Cover Letters');
    }

    public static function getNavigationLabel(): string
    {
        return __('cover_letter_templates');
    }

    public static function getModelLabel(): string
    {
        return __('cover_letter_templates');
    }

    public static function getPluralModelLabel(): string
    {
        return __('cover_letter_templates');
    }

    public static function form(Schema $schema): Schema
    {
        return CoverLetterTemplateForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CoverLetterTemplateInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoverLetterTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CoverLettersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoverLetterTemplates::route('/'),
            'create' => CreateCoverLetterTemplate::route('/create'),
            'view' => ViewCoverLetterTemplate::route('/{record}'),
            'edit' => EditCoverLetterTemplate::route('/{record}/edit'),
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
