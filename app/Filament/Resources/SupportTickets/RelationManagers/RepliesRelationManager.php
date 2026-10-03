<?php

namespace App\Filament\Resources\SupportTickets\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RepliesRelationManager extends RelationManager
{
    protected static string $relationship = 'replies';

    protected static ?string $title = 'Private replies';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('body')
                ->required()
                ->rows(6)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('admin.name')->label(__('Admin')),
                TextColumn::make('body')->limit(80),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['admin_user_id'] = Auth::id();

                        return $data;
                    }),
            ]);
    }
}
