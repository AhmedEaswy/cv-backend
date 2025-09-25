<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),

                // Personal Information Fields
                TextInput::make('info.address')
                    ->label('Address'),
                TextInput::make('info.phone')
                    ->label('Phone')
                    ->tel(),
                TextInput::make('info.email')
                    ->label('Email')
                    ->email(),
                TextInput::make('info.website')
                    ->label('Website')
                    ->url(),
                Textarea::make('info.summary')
                    ->label('Summary')
                    ->rows(3),
                Textarea::make('info.bio')
                    ->label('Bio')
                    ->rows(4),
                TextInput::make('info.portfolio')
                    ->label('Portfolio')
                    ->url(),
                Select::make('info.military_status')
                    ->label('Military Status')
                    ->options([
                        'exempted' => 'Exempted',
                        'completed' => 'Completed',
                        'postponed' => 'Postponed',
                        'not_applicable' => 'Not Applicable',
                    ]),
                Checkbox::make('info.ready_to_relocate')
                    ->label('Ready to Relocate'),

                // Interests Repeater
                Repeater::make('interests')
                    ->label('Interests')
                    ->schema([
                        TextInput::make('interest')
                            ->label('Interest')
                            ->required(),
                    ])
                    ->addActionLabel('Add Interest')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['interest'] ?? null),

                // Languages Repeater
                Repeater::make('languages')
                    ->label('Languages')
                    ->schema([
                        TextInput::make('language')
                            ->label('Language')
                            ->required(),
                        Select::make('level')
                            ->label('Level')
                            ->options([
                                'beginner' => 'Beginner',
                                'intermediate' => 'Intermediate',
                                'advanced' => 'Advanced',
                                'native' => 'Native',
                                'fluent' => 'Fluent',
                            ])
                            ->required(),
                    ])
                    ->addActionLabel('Add Language')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['language'] ?? null)
                    ->columns(2),

                // Work Experience Repeater
                Repeater::make('experiences')
                    ->label('Work Experience')
                    ->schema([
                        TextInput::make('name')
                            ->label('Company/Organization')
                            ->placeholder('Company name'),
                        TextInput::make('location')
                            ->label('Location')
                            ->placeholder('City, Country'),
                        TextInput::make('position')
                            ->label('Position')
                            ->required(),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->placeholder('Job responsibilities and achievements'),
                        DatePicker::make('from')
                            ->label('Start Date'),
                        DatePicker::make('to')
                            ->label('End Date'),
                        Checkbox::make('currentlyWorkingHere')
                            ->label('Currently Working Here')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('to', null);
                                }
                            }),
                    ])
                    ->addActionLabel('Add Experience')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['position'] ?? null)
                    ->columns(2)
                    ->columnSpanFull(),

                // Projects Repeater
                Repeater::make('projects')
                    ->label('Projects')
                    ->schema([
                        TextInput::make('name')
                            ->label('Project Name')
                            ->required(),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->placeholder('Project description and technologies used'),
                        TextInput::make('url')
                            ->label('Project URL')
                            ->url()
                            ->placeholder('https://example.com'),
                        DatePicker::make('from')
                            ->label('Start Date'),
                        DatePicker::make('to')
                            ->label('End Date'),
                    ])
                    ->addActionLabel('Add Project')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                    ->columns(2)
                    ->columnSpanFull(),

                // Education Repeater
                Repeater::make('educations')
                    ->label('Education')
                    ->schema([
                        TextInput::make('institution')
                            ->label('Institution')
                            ->required(),
                        TextInput::make('degree')
                            ->label('Degree')
                            ->required(),
                        TextInput::make('fieldOfStudy')
                            ->label('Field of Study')
                            ->required(),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->placeholder('Additional details about your education'),
                        DatePicker::make('from')
                            ->label('Start Date'),
                        DatePicker::make('to')
                            ->label('End Date'),
                    ])
                    ->addActionLabel('Add Education')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => ($state['degree'] ?? '') . ' - ' . ($state['institution'] ?? ''))
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
