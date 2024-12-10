<?php

namespace App\Filament\Resources;

use App\Enums\NotificationRecipientsTypesEnum;
use App\Filament\Resources\NotificationResource\Pages;
use App\Filament\Resources\NotificationResource\RelationManagers;
use App\Jobs\SendPushNotification;
use App\Jobs\SendPushNotificationJob;
use App\Models\PushNotification;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;
use Filament\Notifications\Notification;

class NotificationResource extends Resource
{
    protected static ?string $model = PushNotification::class;
    protected static ?string $pollingInterval = '5s';

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?int $navigationSort = 30;

    public static function getModelLabel(): string
    {
        return __('notification');
    }

    public static function getPluralModelLabel(): string
    {
        return __('notifications');
    }

    public static function getNavigationLabel(): string
    {
        return self::getPluralModelLabel();
    }

    public static function getPluralLabel(): string
    {
        return self::getPluralModelLabel();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\ToggleButtons::make('recipients_type')
                    ->inline()
                    ->options(NotificationRecipientsTypesEnum::class)
                    ->enum(NotificationRecipientsTypesEnum::class)
                    ->live()
                    ->required(),

                Forms\Components\Select::make('recipients')
                    ->label('Users')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn($record) => User::whereIn('id', $record->recipients)->pluck('name', 'id'))
                    ->hidden(fn(Get $get): bool => $get('recipients_type') !== NotificationRecipientsTypesEnum::SELECTED->value),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('do')
                    ->directory('notifications')
                    ->imageEditor()
                    ->imageEditorEmptyFillColor('#000000'),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('name')),
                Tables\Columns\TextColumn::make('title')->label(__('title')),
                Tables\Columns\TextColumn::make('sender.name')->label(__('sender')),
                Tables\Columns\ImageColumn::make('image')->label(__('image'))
                    ->placeholder(__('no image')),
                Tables\Columns\TextColumn::make('recipients_type')->label(__('sent to'))
                    ->badge()
                    ->formatStateUsing(fn($state) => NotificationRecipientsTypesEnum::tryFrom($state)->getLabel()),
                Tables\Columns\IconColumn::make('is_sent')->label(__('is delivered'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label(__('sent date'))->dateTime()
                    ->formatStateUsing(fn($record) => $record->is_sent ? $record->updated_at : '-'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('resend')
                    ->label(__('resend'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn($record) => !$record->is_sent)
                    ->action(function ($record) {
                        if (!$record->is_sent) {
                            SendPushNotificationJob::dispatch($record);
                            Notification::make()
                                ->title(__('notification added to queue successfully'))
                                ->success()
                                ->send();
                        }
                        (new Pages\ListNotifications)->refreshData();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
        ];
    }
}
