<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Enums\ContactMessageModerationStatus;
use App\Models\ContactMessage;
use App\Services\Contact\ContactMessageDeliveryService;
use App\Services\Contact\ContactSpamService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('publicProfile.slug')->label(__('Profile')),
                TextColumn::make('name'),
                TextColumn::make('email')->searchable(),
                TextColumn::make('subject')->limit(40),
                TextColumn::make('message')
                    ->label(__('Message'))
                    ->wrap()
                    ->limit(80)
                    ->searchable(),
                TextColumn::make('moderation_status')->badge(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('moderation_status')
                    ->options([
                        ContactMessageModerationStatus::Approved->value => __('Approved'),
                        ContactMessageModerationStatus::PendingReview->value => __('Pending review'),
                        ContactMessageModerationStatus::RejectedSpam->value => __('Rejected spam'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('approve')
                    ->label(__('Mark OK'))
                    ->visible(fn (ContactMessage $record) => $record->moderation_status === ContactMessageModerationStatus::PendingReview)
                    ->action(function (ContactMessage $record) {
                        DB::transaction(function () use ($record) {
                            $record->forceFill([
                                'moderation_status' => ContactMessageModerationStatus::Approved,
                                'is_spam' => false,
                                'hidden_at' => null,
                            ])->save();

                            $profile = $record->publicProfile;
                            if ($profile) {
                                app(ContactMessageDeliveryService::class)->deliver($record->fresh(), $profile);
                            }
                        });
                    }),
                Action::make('spam')
                    ->label(__('Spam & block sender'))
                    ->color('danger')
                    ->visible(fn (ContactMessage $record) => $record->moderation_status === ContactMessageModerationStatus::PendingReview)
                    ->action(function (ContactMessage $record) {
                        DB::transaction(function () use ($record) {
                            $record->forceFill([
                                'moderation_status' => ContactMessageModerationStatus::RejectedSpam,
                                'is_spam' => true,
                                'hidden_at' => now(),
                            ])->save();

                            app(ContactSpamService::class)->block(
                                $record->email,
                                $record->ip_address,
                                'admin_review',
                                $record->id,
                            );
                        });
                    }),
                Action::make('block_sender')
                    ->label(__('Block sender'))
                    ->color('danger')
                    ->action(function (ContactMessage $record) {
                        app(ContactSpamService::class)->block(
                            $record->email,
                            $record->ip_address,
                            'manual',
                            $record->id,
                        );
                    }),
                DeleteAction::make()->label(__('Remove')),
            ]);
    }
}
