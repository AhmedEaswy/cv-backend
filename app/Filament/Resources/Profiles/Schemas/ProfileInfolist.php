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

                // Profile Information
                TextEntry::make('name')
                    ->label('CV Name')
                    ->weight('bold')
                    ->icon('heroicon-m-document-text'),
                TextEntry::make('language')
                    ->label('Language')
                    ->badge()
                    ->icon('heroicon-m-language'),


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

                // KeyValueEntry::make('info')
                //     ->label('Personal Information')
                //     ->formatStateUsing(function ($state) {
                //         if (!is_array($state)) {
                //             return [];
                //         }
                //         dd($state);

                //         // Convert all values to strings to avoid htmlspecialchars error
                //         $formatted = [];
                //         foreach ($state as $key => $value) {
                //             if (is_array($value)) {
                //                 // Convert array to readable string format
                //                 if (isset($value[0]) && is_array($value[0])) {
                //                     // Array of arrays (like skills)
                //                     $items = array_map(function($item) {
                //                         if (is_array($item) && isset($item['name'])) {
                //                             return $item['name'];
                //                         }
                //                         return json_encode($item);
                //                     }, $value);
                //                     $formatted[$key] = implode(', ', $items);
                //                 } else {
                //                     $formatted[$key] = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                //                 }
                //             } elseif ($value === null) {
                //                 $formatted[$key] = '';
                //             } else {
                //                 $formatted[$key] = (string) $value;
                //             }
                //         }
                //         return $formatted;
                //     }),


                // Interests

                // RepeatableEntry::make('interests')
                //     ->label('Interests')
                //     ->schema([
                //         TextEntry::make('interest')
                //             ->label('Interest')
                //             ->formatStateUsing(fn ($state) => is_string($state) ? $state : ''),
                //     ])
                //     ->contained(false),

                // Languages

                // RepeatableEntry::make('languages')
                //     ->label('Languages')
                //     ->schema([
                //         TextEntry::make('language')
                //             ->label('Language')
                //             ->formatStateUsing(fn ($state) => is_string($state) ? $state : ''),
                //         TextEntry::make('level')
                //             ->label('Level')
                //             ->badge()
                //             ->color(function ($state) {
                //                 if (!is_string($state)) {
                //                     return 'gray';
                //                 }
                //                 return match ($state) {
                //                     'native', 'fluent' => 'success',
                //                     'advanced' => 'info',
                //                     'intermediate' => 'warning',
                //                     'beginner' => 'gray',
                //                     default => 'gray',
                //                 };
                //             })
                //             ->formatStateUsing(function ($state) {
                //                 if (!is_string($state)) {
                //                     return 'Unknown';
                //                 }
                //                 return ucfirst($state);
                //             }),
                //     ])
                //     ->contained(false),

                // Work Experience

                // RepeatableEntry::make('experiences')
                //     ->label('Work Experience')
                //     ->schema([
                //         TextEntry::make('position')
                //             ->label('Position')
                //             ->weight('bold')
                //             ->formatStateUsing(fn ($state) => is_string($state) ? $state : ''),
                //         TextEntry::make('name')
                //             ->label('Company/Organization')
                //             ->icon('heroicon-m-building-office')
                //             ->formatStateUsing(fn ($state) => is_string($state) ? $state : ''),
                //         TextEntry::make('location')
                //             ->label('Location')
                //             ->icon('heroicon-m-map-pin')
                //             ->formatStateUsing(fn ($state) => is_string($state) ? $state : ''),
                //         TextEntry::make('description')
                //             ->label('Description')
                //             ->html()
                //             ->columnSpanFull()
                //             ->formatStateUsing(fn ($state) => is_string($state) ? $state : ''),
                //         TextEntry::make('from')
                //             ->label('Start Date')
                //             ->icon('heroicon-m-calendar')
                //             ->formatStateUsing(function ($state) {
                //                 if (!$state || !is_string($state)) return 'N/A';
                //                 // Handle Y-m format (e.g., "2021-03")
                //                 if (preg_match('/^\d{4}-\d{2}$/', $state)) {
                //                     return date('M Y', strtotime($state . '-01'));
                //                 }
                //                 return date('M Y', strtotime($state));
                //             }),
                //         TextEntry::make('to')
                //             ->label('End Date')
                //             ->icon('heroicon-m-calendar')
                //             ->placeholder('Present')
                //             ->formatStateUsing(function ($state, $record) {
                //                 if ($record['currentlyWorkingHere'] ?? false) {
                //                     return 'Present';
                //                 }
                //                 if (!$state || !is_string($state)) return 'N/A';
                //                 // Handle Y-m format (e.g., "2021-03")
                //                 if (preg_match('/^\d{4}-\d{2}$/', $state)) {
                //                     return date('M Y', strtotime($state . '-01'));
                //                 }
                //                 return date('M Y', strtotime($state));
                //             }),
                //         TextEntry::make('currentlyWorkingHere')
                //             ->label('Currently Working')
                //             ->badge()
                //             ->color(fn ($state): string => ($state === true || $state === 1 || $state === '1') ? 'success' : 'gray')
                //             ->formatStateUsing(function ($state) {
                //                 if (is_bool($state)) {
                //                     return $state ? 'Yes' : 'No';
                //                 }
                //                 if ($state === 1 || $state === '1' || $state === true) {
                //                     return 'Yes';
                //                 }
                //                 return 'No';
                //             }),
                //     ])
                //     ->contained(false)
                //     ->columns(2),

                // Projects

                // RepeatableEntry::make('projects')
                //     ->label('Projects')
                //     ->schema([
                //         TextEntry::make('name')
                //             ->label('Project Name')
                //             ->weight('bold')
                //             ->formatStateUsing(fn ($state) => is_string($state) ? $state : ''),
                //         TextEntry::make('description')
                //             ->label('Description')
                //             ->html()
                //             ->columnSpanFull()
                //             ->formatStateUsing(fn ($state) => is_string($state) ? $state : ''),
                //         TextEntry::make('url')
                //             ->label('Project URL')
                //             ->url(fn (?string $state): ?string => is_string($state) ? $state : null)
                //             ->openUrlInNewTab()
                //             ->icon('heroicon-m-link')
                //             ->formatStateUsing(fn ($state) => is_string($state) ? $state : ''),
                //         TextEntry::make('from')
                //             ->label('Start Date')
                //             ->icon('heroicon-m-calendar')
                //             ->formatStateUsing(function ($state) {
                //                 if (!$state || !is_string($state)) return 'N/A';
                //                 // Handle Y-m format (e.g., "2021-03")
                //                 if (preg_match('/^\d{4}-\d{2}$/', $state)) {
                //                     return date('M Y', strtotime($state . '-01'));
                //                 }
                //                 return date('M Y', strtotime($state));
                //             }),
                //         TextEntry::make('to')
                //             ->label('End Date')
                //             ->icon('heroicon-m-calendar')
                //             ->placeholder('Present')
                //             ->formatStateUsing(function ($state) {
                //                 if (!$state || !is_string($state)) return 'N/A';
                //                 // Handle Y-m format (e.g., "2021-03")
                //                 if (preg_match('/^\d{4}-\d{2}$/', $state)) {
                //                     return date('M Y', strtotime($state . '-01'));
                //                 }
                //                 return date('M Y', strtotime($state));
                //             }),
                //     ])
                //     ->contained(false)
                //     ->columns(2),

                // Education

                // RepeatableEntry::make('educations')
                //     ->label('Education')
                //     ->schema([
                //         TextEntry::make('degree')
                //             ->label('Degree')
                //             ->weight('bold')
                //             ->formatStateUsing(fn ($state) => is_string($state) ? $state : ''),
                //         TextEntry::make('institution')
                //             ->label('Institution')
                //             ->icon('heroicon-m-academic-cap')
                //             ->formatStateUsing(fn ($state) => is_string($state) ? $state : ''),
                //         TextEntry::make('fieldOfStudy')
                //             ->label('Field of Study')
                //             ->icon('heroicon-m-book-open')
                //             ->formatStateUsing(fn ($state) => is_string($state) ? $state : ''),
                //         TextEntry::make('description')
                //             ->label('Description')
                //             ->html()
                //             ->columnSpanFull()
                //             ->formatStateUsing(fn ($state) => is_string($state) ? $state : ''),
                //         TextEntry::make('from')
                //             ->label('Start Date')
                //             ->icon('heroicon-m-calendar')
                //             ->formatStateUsing(function ($state) {
                //                 if (!$state || !is_string($state)) return 'N/A';
                //                 // Handle Y-m format (e.g., "2021-03")
                //                 if (preg_match('/^\d{4}-\d{2}$/', $state)) {
                //                     return date('M Y', strtotime($state . '-01'));
                //                 }
                //                 return date('M Y', strtotime($state));
                //             }),
                //         TextEntry::make('to')
                //             ->label('End Date')
                //             ->icon('heroicon-m-calendar')
                //             ->placeholder('Present')
                //             ->formatStateUsing(function ($state) {
                //                 if (!$state || !is_string($state)) return 'N/A';
                //                 // Handle Y-m format (e.g., "2021-03")
                //                 if (preg_match('/^\d{4}-\d{2}$/', $state)) {
                //                     return date('M Y', strtotime($state . '-01'));
                //                 }
                //                 return date('M Y', strtotime($state));
                //             }),
                //     ])
                //     ->contained(false)
                //     ->columns(2),

                // Sections Order

                // TextEntry::make('sections_order')
                //     ->label('Sections Order')
                //     ->badge()
                //     ->separator(',')
                //     ->formatStateUsing(function ($state) {
                //         if (!is_array($state)) {
                //             return ['Not set'];
                //         }
                //         // Convert array values to strings to avoid htmlspecialchars error
                //         return array_map(fn($item) => is_string($item) ? $item : (string)$item, $state);
                //     }),




                ]);
    }
}
