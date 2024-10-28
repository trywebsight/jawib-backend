<?php

namespace App\Filament\Resources\Store;

use App\Enums\StoreOrderStatusEnum;
use App\Enums\TapPaymentStatusEnum;
use App\Filament\Resources\Store\StoreOrderResource\Pages;
use App\Filament\Resources\Store\StoreOrderResource\RelationManagers\OrderItemsRelationManager;
use App\Models\Store\StoreOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StoreOrderResource extends Resource
{
    protected static ?string $model = StoreOrder::class;

    protected static ?int $navigationSort = 210;

    public static function getNavigationLabel(): string
    {
        return __('orders');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('store');
    }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->label(__('Order ID'))
                    ->disabled(),

                Forms\Components\Select::make('user_id')
                    ->label(__('User'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->disabled(),

                Forms\Components\TextInput::make('shipping')
                    ->label(__('Shipping'))
                    ->numeric()
                    ->disabled(),

                Forms\Components\TextInput::make('total')
                    ->label(__('Total'))
                    ->numeric()
                    ->disabled(),

                Forms\Components\TextInput::make('tap_id')
                    ->label(__('Tap ID'))
                    ->disabled(),

                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(StoreOrderStatusEnum::class)
                    ->required(),

                Forms\Components\Select::make('payment_status')
                    ->label(__('Payment Status'))
                    ->options(TapPaymentStatusEnum::class)
                    ->disabled(),

                Forms\Components\DateTimePicker::make('created_at')
                    ->label(__('Created At'))
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('Order ID'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('User'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('total')
                    ->label(__('Total'))
                    ->money('usd')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->translateLabel()
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->translateLabel()
                    ->badge()
                    ->color(TapPaymentStatusEnum::class)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // You can add bulk actions if needed
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoreOrders::route('/'),
            'view' => Pages\ViewStoreOrder::route('/{record}'),
            'edit' => Pages\EditStoreOrder::route('/{record}/edit'),
        ];
    }
}
