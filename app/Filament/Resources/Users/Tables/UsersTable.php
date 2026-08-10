<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Notifications\AdminPasswordSetupNotification;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(function ($record): string {
                        if ($record->trashed()) {
                            return 'deleted';
                        }
                        if ($record->password !== null && $record->password !== '') {
                            return 'active';
                        }

                        return 'invited';
                    })
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'deleted' => 'danger',
                        'invited' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Registered at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('activated_at')
                    ->label('Activated at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
                ActionGroup::make([
                    Action::make('resend_invitation')
                        ->label('Resend Invitation')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Resend Invitation')
                        ->modalDescription('Are you sure you want to resend the email invitation?')
                        ->modalSubmitActionLabel('Yes, resend it')
                        ->action(function (User $record) {
                            $record->notify(new AdminPasswordSetupNotification);
                            Notification::make()
                                ->title('Invitation resent successfully.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (User $record) => $record->password === null)
                        ->authorize('update'),
                    Action::make('reset_password')
                        ->label('Reset Password')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Reset Password')
                        ->modalDescription('Are you sure you want to send a password reset email to this user?')
                        ->modalSubmitActionLabel('Yes, send email')
                        ->action(function (User $record) {
                            $status = Password::sendResetLink(['email' => $record->email]);
                            if ($status === Password::RESET_LINK_SENT) {
                                Notification::make()
                                    ->title('Password reset email sent.')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title(__($status))
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn (User $record) => $record->password !== null)
                        ->authorize('update'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
