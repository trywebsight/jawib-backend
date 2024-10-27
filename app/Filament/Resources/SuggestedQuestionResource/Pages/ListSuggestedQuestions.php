<?php

namespace App\Filament\Resources\SuggestedQuestionResource\Pages;

use App\Filament\Resources\SuggestedQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuggestedQuestions extends ListRecords
{
    protected static string $resource = SuggestedQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
