<?php

namespace App\Filament\Resources;

use App\Enums\TapStatusEnum;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Package;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-c-arrows-right-left';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('package_id')
                    ->relationship('package', 'title')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $package = Package::find($state);
                        if ($package) {
                            $set('credit_change', $package->games_count);
                        }
                    }),
                Forms\Components\Hidden::make('credit_change')
                    ->required(),
                Forms\Components\Select::make('payment_status')
                    ->options([
                        TapStatusEnum::INITIATED        => __(TapStatusEnum::INITIATED),
                        TapStatusEnum::CAPTURED         => __(TapStatusEnum::CAPTURED),
                        TapStatusEnum::CANCELLED        => __(TapStatusEnum::CANCELLED),
                    ])
                    ->required(),
            ]);
    }

    public static function tableColumns()
    {
        return [
            Tables\Columns\TextColumn::make('id')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('user.name')
                ->label(__('user'))
                ->sortable(),
            Tables\Columns\TextColumn::make('package.title')
                ->label(__('package'))
                ->sortable(),
            // Tables\Columns\TextColumn::make('credit_change')
            //     ->numeric()
            //     ->sortable()
            //     ->toggleable(isToggledHiddenByDefault: true),
            // Tables\Columns\TextColumn::make('type')
            //     ->searchable()
            //     ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('tap_id')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
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

        ];
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::tableColumns())
            ->filters([
                SelectFilter::make('payment_status')
                    ->label(__('payment status'))
                    ->options([
                        TapStatusEnum::INITIATED    => __(TapStatusEnum::INITIATED),
                        TapStatusEnum::CAPTURED     => __(TapStatusEnum::CAPTURED),
                        TapStatusEnum::CANCELLED    => __(TapStatusEnum::CANCELLED),
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
