<?php

namespace App\Filament\Resources\PublicProfiles\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PublicProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('public_profile_template_id')
                    ->relationship(
                        'template',
                        'name',
                        fn ($query) => $query->where('is_active', true)
                    )
                    ->label('Template')
                    ->searchable()
                    ->preload(),
                TextInput::make('slug')
                    ->required()
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),
                Checkbox::make('is_public')
                    ->label('Public')
                    ->helperText('Visible at /u/{slug}')
                    ->default(true),
                Select::make('language')
                    ->options([
                        'en' => 'English',
                        'ar' => 'Arabic',
                        'tr' => 'Turkish',
                    ])
                    ->default('en')
                    ->required(),
                TextInput::make('headline')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('about')
                    ->rows(4)
                    ->columnSpanFull(),

                KeyValue::make('info')
                    ->label('Identity & contact')
                    ->keyLabel('Field')
                    ->valueLabel('Value')
                    ->reorderable()
                    ->columnSpanFull(),

                KeyValue::make('social_links')
                    ->label('Social links')
                    ->keyLabel('Platform')
                    ->valueLabel('URL')
                    ->columnSpanFull(),

                KeyValue::make('availability')
                    ->label('Availability')
                    ->columnSpanFull(),

                KeyValue::make('cta')
                    ->label('Call to action')
                    ->columnSpanFull(),

                KeyValue::make('seo')
                    ->label('SEO')
                    ->columnSpanFull(),

                Repeater::make('experiences')
                    ->schema([
                        TextInput::make('position')->required(),
                        TextInput::make('company')->required(),
                        TextInput::make('from')->placeholder('YYYY-MM'),
                        TextInput::make('to')->placeholder('YYYY-MM'),
                        Checkbox::make('current'),
                        Textarea::make('description')->rows(2)->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->columnSpanFull(),

                Repeater::make('projects')
                    ->schema([
                        TextInput::make('title')->required(),
                        TextInput::make('url'),
                        TextInput::make('image'),
                        Textarea::make('description')->rows(2)->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->columnSpanFull(),

                Repeater::make('skills')
                    ->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('category'),
                        TextInput::make('level'),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->columnSpanFull(),

                Repeater::make('services')
                    ->schema([
                        TextInput::make('title')->required(),
                        Textarea::make('description')->rows(2),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                Repeater::make('educations')
                    ->schema([
                        TextInput::make('institution'),
                        TextInput::make('degree'),
                        TextInput::make('fieldOfStudy'),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->columnSpanFull(),

                Repeater::make('testimonials')
                    ->schema([
                        Textarea::make('quote')->rows(2)->required(),
                        TextInput::make('author'),
                        TextInput::make('role'),
                        TextInput::make('company'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }
}
