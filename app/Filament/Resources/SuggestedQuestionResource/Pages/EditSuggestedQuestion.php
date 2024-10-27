<?php

namespace App\Filament\Resources\SuggestedQuestionResource\Pages;

use App\Filament\Resources\SuggestedQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuggestedQuestion extends EditRecord
{
    protected static string $resource = SuggestedQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
