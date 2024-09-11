<?php

namespace App\Filament\Resources\QuestionFeedbackResource\Pages;

use App\Filament\Resources\QuestionFeedbackResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuestionFeedback extends EditRecord
{
    protected static string $resource = QuestionFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
