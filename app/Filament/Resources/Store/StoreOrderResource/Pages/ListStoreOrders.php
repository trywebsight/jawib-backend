<?php

namespace App\Filament\Resources\Store\StoreOrderResource\Pages;

use App\Filament\Resources\Store\StoreOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStoreOrders extends ListRecords
{
    protected static string $resource = StoreOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
