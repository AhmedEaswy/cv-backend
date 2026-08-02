<?php

namespace App\Filament\Resources\AtsChecks\Schemas;

use App\Filament\Resources\Profiles\ProfileResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AtsCheckInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Score summary'))
                    ->schema([
                        TextEntry::make('score')
                            ->badge()
                            ->color(fn (int $state): string => match (true) {
                                $state >= 85 => 'success',
                                $state >= 70 => 'info',
                                $state >= 55 => 'warning',
                                default => 'danger',
                            }),
                        TextEntry::make('grade')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'A' => 'success',
                                'B' => 'info',
                                'C' => 'warning',
                                'D' => 'gray',
                                default => 'danger',
                            }),
                        TextEntry::make('source')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                        TextEntry::make('language')
                            ->badge()
                            ->placeholder('—'),
                        IconEntry::make('has_job_description')
                            ->label('Job description')
                            ->boolean(),
                        TextEntry::make('keyword_coverage')
                            ->label('Keyword coverage')
                            ->suffix('%')
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label('Checked at')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make(__('Candidate'))
                    ->schema([
                        TextEntry::make('candidate_name')
                            ->label('Name')
                            ->placeholder('—'),
                        TextEntry::make('candidate_email')
                            ->label('Email')
                            ->placeholder('—'),
                        TextEntry::make('user.name')
                            ->label('User')
                            ->url(fn ($record) => $record->user_id
                                ? UserResource::getUrl('view', ['record' => $record->user_id])
                                : null)
                            ->placeholder(__('Anonymous')),
                        TextEntry::make('profile.name')
                            ->label('CV profile')
                            ->url(fn ($record) => $record->profile_id
                                ? ProfileResource::getUrl('view', ['record' => $record->profile_id])
                                : null)
                            ->placeholder('—'),
                        TextEntry::make('pdf_original_name')
                            ->label('PDF filename')
                            ->placeholder('—')
                            ->visible(fn ($record): bool => $record->source === 'pdf'),
                        TextEntry::make('ip_address')->placeholder('—'),
                        TextEntry::make('country')->placeholder('—'),
                        TextEntry::make('device')->placeholder('—')->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('Category scores'))
                    ->schema([
                        TextEntry::make('categories')
                            ->label('')
                            ->formatStateUsing(function ($state): string {
                                if (! is_array($state) || $state === []) {
                                    return '—';
                                }

                                return collect($state)
                                    ->map(fn ($score, $category) => ucfirst(str_replace('_', ' ', (string) $category)).': '.$score.'%')
                                    ->implode(' · ');
                            })
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record): bool => filled($record->categories)),

                Section::make(__('Checks'))
                    ->schema([
                        RepeatableEntry::make('checks')
                            ->schema([
                                TextEntry::make('id')
                                    ->label('Check')
                                    ->formatStateUsing(fn ($state): string => str_replace('_', ' ', (string) $state)),
                                TextEntry::make('category')->badge(),
                                IconEntry::make('passed')->boolean()->label('Passed'),
                                TextEntry::make('weight')->label('Weight'),
                                TextEntry::make('message')->columnSpanFull(),
                                TextEntry::make('tip')
                                    ->placeholder('—')
                                    ->columnSpanFull()
                                    ->visible(fn ($state): bool => filled($state)),
                            ])
                            ->columns(4),
                    ])
                    ->columnSpanFull()
                    ->visible(fn ($record): bool => filled($record->checks)),

                Section::make(__('Keyword fit'))
                    ->schema([
                        TextEntry::make('keywords.coverage_percent')
                            ->label('Coverage')
                            ->suffix('%')
                            ->placeholder('—'),
                        TextEntry::make('keywords.matched')
                            ->label('Matched')
                            ->formatStateUsing(fn ($state): string => is_array($state) ? implode(', ', $state) : '—')
                            ->columnSpanFull(),
                        TextEntry::make('keywords.missing')
                            ->label('Missing')
                            ->formatStateUsing(fn ($state): string => is_array($state) ? implode(', ', $state) : '—')
                            ->columnSpanFull(),
                        TextEntry::make('job_description')
                            ->label('Job description')
                            ->prose()
                            ->columnSpanFull()
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record): bool => (bool) $record->has_job_description),
            ]);
    }
}
