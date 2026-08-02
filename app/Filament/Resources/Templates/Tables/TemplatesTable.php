<?php

namespace App\Filament\Resources\Templates\Tables;

use App\Filament\Resources\Templates\TemplateResource;
use App\Filament\Support\ConfirmableToggle;
use App\Filament\Support\ImagePlaceholder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                ImageColumn::make('preview')
                    ->label('Preview')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(ImagePlaceholder::url())
                    ->extraImgAttributes(ImagePlaceholder::imgAttributes()),
                ConfirmableToggle::make('is_active', TemplateResource::class, 'Active')
                    ->sortable(),
                ConfirmableToggle::make('is_default', TemplateResource::class, 'Default')
                    ->sortable(),
                ConfirmableToggle::make('supports_image', TemplateResource::class, 'Photo')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                TernaryFilter::make('is_active')
                    ->label('Active Status'),
                TernaryFilter::make('is_default')
                    ->label('Default Template'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
