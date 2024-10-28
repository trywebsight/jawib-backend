<?php

namespace App\Filament\Resources\Store\StoreProductResource\Pages;

use App\Filament\Resources\Store\StoreProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStoreProduct extends EditRecord
{
    protected static string $resource = StoreProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
