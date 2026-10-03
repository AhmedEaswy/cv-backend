<?php

namespace App\Filament\Resources\HelpArticles;

use App\Filament\Resources\HelpArticles\Pages\CreateHelpArticle;
use App\Filament\Resources\HelpArticles\Pages\EditHelpArticle;
use App\Filament\Resources\HelpArticles\Pages\ListHelpArticles;
use App\Filament\Resources\HelpArticles\Schemas\HelpArticleForm;
use App\Filament\Resources\HelpArticles\Tables\HelpArticlesTable;
use App\Models\HelpArticle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class HelpArticleResource extends Resource
{
    protected static ?string $model = HelpArticle::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static string|UnitEnum|null $navigationGroup = 'Support';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'slug';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Support');
    }

    public static function getNavigationLabel(): string
    {
        return __('Help articles');
    }

    public static function form(Schema $schema): Schema
    {
        return HelpArticleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HelpArticlesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHelpArticles::route('/'),
            'create' => CreateHelpArticle::route('/create'),
            'edit' => EditHelpArticle::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) HelpArticle::query()->count();
    }
}
