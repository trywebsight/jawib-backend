<?php

namespace App\Filament\Resources\QuestionFeedbackResource\Pages;

use App\Filament\Resources\QuestionFeedbackResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewQuestionFeedback extends ViewRecord
{
    protected static string $resource = QuestionFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
