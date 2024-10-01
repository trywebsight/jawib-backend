<?php

namespace App\Filament\Resources\GameResource\Pages;

use App\Filament\Resources\GameResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Services\GameService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Game;

class CreateGame extends CreateRecord
{
    protected static string $resource = GameResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $gameService = (new GameService);

        try {
            $user = User::findOrFail($data['user_id']);
            $game = $gameService->createGame($user, [
                'title' => $data['title'],
                'categories' => $data['categories'],
            ]);

            Notification::make()
                ->title('Game created successfully')
                ->success()
                ->send();

            return $game;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error creating game')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Uncomment and modify if needed
    // protected function afterCreate(): void
    // {
    //     // Any additional logic after game creation
    // }
}
