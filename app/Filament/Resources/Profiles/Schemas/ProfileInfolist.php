<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // User Information
                TextEntry::make('user.name')
                    ->label('User Name'),
                TextEntry::make('user.email')
                    ->label('User Email')
                    ->icon('heroicon-m-envelope'),
                TextEntry::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->icon('heroicon-m-calendar'),
                TextEntry::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->icon('heroicon-m-pencil'),

                // Personal Information
                KeyValueEntry::make('info')
                    ->label('Personal Information'),

                // Interests
                RepeatableEntry::make('interests')
                    ->label('Interests')
                    ->schema([
                        TextEntry::make('interest')
                            ->label('Interest'),
                    ])
                    ->contained(false),

                // Languages
                RepeatableEntry::make('languages')
                    ->label('Languages')
                    ->schema([
                        TextEntry::make('language')
                            ->label('Language'),
                        TextEntry::make('level')
                            ->label('Level')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'native', 'fluent' => 'success',
                                'advanced' => 'info',
                                'intermediate' => 'warning',
                                'beginner' => 'gray',
                                default => 'gray',
                            }),
                    ])
                    ->contained(false),

                // Work Experience
                RepeatableEntry::make('experiences')
                    ->label('Work Experience')
                    ->schema([
                        TextEntry::make('position')
                            ->label('Position')
                            ->weight('bold'),
                        TextEntry::make('name')
                            ->label('Company/Organization')
                            ->icon('heroicon-m-building-office'),
                        TextEntry::make('location')
                            ->label('Location')
                            ->icon('heroicon-m-map-pin'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->html()
                            ->columnSpanFull(),
                        TextEntry::make('from')
                            ->label('Start Date')
                            ->date()
                            ->icon('heroicon-m-calendar'),
                        TextEntry::make('to')
                            ->label('End Date')
                            ->date()
                            ->icon('heroicon-m-calendar')
                            ->placeholder('Present')
                            ->formatStateUsing(fn (?string $state, $record): string =>
                                $state ? date('M Y', strtotime($state)) :
                                ($record['currentlyWorkingHere'] ?? false ? 'Present' : '')
                            ),
                        TextEntry::make('currentlyWorkingHere')
                            ->label('Currently Working')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No'),
                    ])
                    ->contained(false)
                    ->columns(2),

                // Projects
                RepeatableEntry::make('projects')
                    ->label('Projects')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Project Name')
                            ->weight('bold'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->html()
                            ->columnSpanFull(),
                        TextEntry::make('url')
                            ->label('Project URL')
                            ->url(fn (?string $state): ?string => $state)
                            ->openUrlInNewTab()
                            ->icon('heroicon-m-link'),
                        TextEntry::make('from')
                            ->label('Start Date')
                            ->date()
                            ->icon('heroicon-m-calendar'),
                        TextEntry::make('to')
                            ->label('End Date')
                            ->date()
                            ->icon('heroicon-m-calendar')
                            ->placeholder('Present'),
                    ])
                    ->contained(false)
                    ->columns(2),

                // Education
                RepeatableEntry::make('educations')
                    ->label('Education')
                    ->schema([
                        TextEntry::make('degree')
                            ->label('Degree')
                            ->weight('bold'),
                        TextEntry::make('institution')
                            ->label('Institution')
                            ->icon('heroicon-m-academic-cap'),
                        TextEntry::make('fieldOfStudy')
                            ->label('Field of Study')
                            ->icon('heroicon-m-book-open'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->html()
                            ->columnSpanFull(),
                        TextEntry::make('from')
                            ->label('Start Date')
                            ->date()
                            ->icon('heroicon-m-calendar'),
                        TextEntry::make('to')
                            ->label('End Date')
                            ->date()
                            ->icon('heroicon-m-calendar')
                            ->placeholder('Present'),
                    ])
                    ->contained(false)
                    ->columns(2),
            ]);
    }
}
