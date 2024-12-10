<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Enums\NotificationRecipientsTypesEnum;
use App\Filament\Resources\NotificationResource;
use App\Jobs\SendPushNotificationJob;
use App\Models\PushNotification;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Services\OneSignalService;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Table;

class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->poll('10s');
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Actions\Action::make('send_notification')
                ->label('Send Notification')
                ->icon('heroicon-o-bell-alert')
                // ->color('gray')
                ->form([

                    Forms\Components\ToggleButtons::make('recipients_type')
                        ->inline()
                        ->options(NotificationRecipientsTypesEnum::class)
                        ->enum(NotificationRecipientsTypesEnum::class)
                        ->live()
                        ->required(),

                    Select::make('recipients')
                        ->label('Users')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->hidden(fn(Get $get): bool => $get('recipients_type') !== NotificationRecipientsTypesEnum::SELECTED->value)
                        ->options(
                            User::query()
                                ->active()
                                ->pluck('name', 'id')
                        ),

                    Section::make(ucfirst(__('notification')))
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->columnSpanFull()
                                ->maxLength(255),
                            TextInput::make('title')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('content')
                                ->required()
                                ->columnSpanFull(),
                            FileUpload::make('image')
                                ->image()
                                ->disk('do')
                                ->directory('notifications')
                                ->imageEditor()
                                ->imageEditorEmptyFillColor('#000000'),
                        ]),
                ])
                ->action(function (array $data) {

                    // dd($data);

                    $notification = PushNotification::firstOrCreate([
                        'recipients_type'   => $data['recipients_type'],
                        'recipients'        => $data['recipients'] ?? null,
                        'sent_by'           => auth('admin')->id(),
                        'name'              => $data['name'],
                        'title'             => $data['title'],
                        'content'           => $data['content'],
                        'image'             => media_url($data['image']),
                    ]);

                    SendPushNotificationJob::dispatch($notification);
                })
                ->after(function () {
                    Notification::make()
                        ->title(__('notification sent to successfully'))
                        ->success()
                        ->send();

                    $this->refreshData();
                }),
        ];
    }

    public function refreshData()
    {
        $this->dispatch('refresh');
    }
}
