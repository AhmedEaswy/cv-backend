<?php

namespace App\Filament\Resources\ContactSpamBlocklists;

use App\Filament\Resources\ContactSpamBlocklists\Pages\ListContactSpamBlocklists;
use App\Filament\Resources\ContactSpamBlocklists\Tables\ContactSpamBlocklistsTable;
use App\Models\ContactSpamBlocklist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ContactSpamBlocklistResource extends Resource
{
    protected static ?string $model = ContactSpamBlocklist::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-no-symbol';

    protected static string|UnitEnum|null $navigationGroup = 'CV Builder';

    protected static ?int $navigationSort = 8;

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('CV Builder');
    }

    public static function getNavigationLabel(): string
    {
        return __('Spam blocklist');
    }

    public static function getModelLabel(): string
    {
        return __('Spam entry');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Spam blocklist');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return ContactSpamBlocklistsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactSpamBlocklists::route('/'),
        ];
    }
}
