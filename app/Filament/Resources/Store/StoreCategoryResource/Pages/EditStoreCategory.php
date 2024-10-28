<?php

namespace App\Filament\Resources\Store\StoreCategoryResource\Pages;

use App\Filament\Resources\Store\StoreCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStoreCategory extends EditRecord
{
    protected static string $resource = StoreCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
