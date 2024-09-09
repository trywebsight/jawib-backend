<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Transaction;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('send_credit_to_user')
                ->label('Send Credit to User')
                ->icon('heroicon-o-currency-dollar')
                ->color('gray')
                ->form([
                    TextInput::make('game_credits')
                        ->label(__('Game credits amount'))
                        ->inputMode('numeric') // Set input mode to numeric
                        ->integer()
                        ->required()
                        ->placeholder('Please provide the amount you want to credit this user.'),
                ])
                ->action(function (array $data) {

                    $user = $this->record;
                    dd($data);

                    // Transaction::create([
                    //     'user_id' => $user->id,

                    // ]);
                    Notification::make()
                        ->title('User Wallet Charged')
                        ->success()
                        ->send();
                }),
        ];
    }
}
