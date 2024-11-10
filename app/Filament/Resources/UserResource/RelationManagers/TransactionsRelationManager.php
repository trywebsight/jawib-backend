<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->toggleable($isToggleHiddenByDefault = true)
                    ->label(__('transaction id')),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('type'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'deposit' => __('deposit'),
                        'withdraw' => __('withdraw'),
                        default => '-',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'deposit' => 'success',
                        'withdraw' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('amount')),
                // ->badge()
                // ->color('success'),
                Tables\Columns\TextColumn::make('meta.description')
                    ->label(__('reason')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
