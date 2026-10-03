<?php

namespace App\Filament\Resources\FeatureRequests\Schemas;

use App\Enums\FeatureRequestStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FeatureRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->disabled(),
            Textarea::make('body')->disabled()->rows(8)->columnSpanFull(),
            Select::make('status')
                ->options([
                    FeatureRequestStatus::UnderReview->value => __('Under review'),
                    FeatureRequestStatus::Planned->value => __('Planned'),
                    FeatureRequestStatus::InProgress->value => __('In progress'),
                    FeatureRequestStatus::Complete->value => __('Complete'),
                ])
                ->required()
                ->native(false),
            Toggle::make('is_published')->label(__('Published on public board')),
            TextInput::make('vote_count')->numeric()->disabled(),
        ]);
    }
}
