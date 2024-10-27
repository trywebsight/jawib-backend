<?php

namespace App\Filament\Resources\SuggestedQuestionResource\Pages;

use App\Filament\Resources\SuggestedQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSuggestedQuestion extends CreateRecord
{
    protected static string $resource = SuggestedQuestionResource::class;
}
