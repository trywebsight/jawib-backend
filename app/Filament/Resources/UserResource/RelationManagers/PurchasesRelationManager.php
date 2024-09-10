<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\TapStatusEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchasesRelationManager extends RelationManager
{
    protected static string $relationship = 'purchases';

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('pruchases')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('package.title')
                    ->label(__('package'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('tap_id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label(__('payment status'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        TapStatusEnum::CAPTURED => __(TapStatusEnum::CAPTURED),
                        TapStatusEnum::CANCELLED => __(TapStatusEnum::CANCELLED),
                        TapStatusEnum::NOT_CAPTURED => __(TapStatusEnum::NOT_CAPTURED),
                        default => 'Pending',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        TapStatusEnum::CAPTURED => 'success',
                        TapStatusEnum::CANCELLED => 'danger',
                        TapStatusEnum::NOT_CAPTURED => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('transaction time'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
