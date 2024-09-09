<?php

namespace App\Filament\Resources\GameResource\Pages;

use App\Filament\Resources\GameResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewGame extends ViewRecord
{
    protected static string $resource = GameResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('user.name')
                    ->label('User'),
                Infolists\Components\TextEntry::make('title'),
                Infolists\Components\TextEntry::make('categories.title')
                    ->badge()
                    ->label('Categories'),
                Infolists\Components\RepeatableEntry::make('questions')
                    ->schema([
                        Infolists\Components\TextEntry::make('id'),
                        Infolists\Components\TextEntry::make('question'),
                        Infolists\Components\TextEntry::make('level')
                            ->label(__('level'))
                            ->formatStateUsing(function ($state) {
                                return match ($state) {
                                    1 => __('easy'),
                                    2 => __('medium'),
                                    3 => __('hard'),
                                    default => __('unknown'),
                                };
                            })
                            ->badge()
                            ->color(function ($state) {
                                return match ($state) {
                                    1 => 'success',
                                    2 => 'warning',
                                    3 => 'primary',
                                    default => 'warning',
                                };
                            }),
                        Infolists\Components\TextEntry::make('category.title')
                            ->label('Category'),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
