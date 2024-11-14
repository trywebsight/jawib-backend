<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Infolists\Components\Section;
use App\Enums\TapPaymentStatusEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function getModelLabel(): string
    {
        return __('order');
    }

    public static function getNavigationLabel(): string
    {
        return __('orders');
    }

    public static function getPluralModelLabel(): string
    {
        return __('orders');
    }

    public static function getPluralLabel(): string
    {
        return __('orders');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('id'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('user'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('total'))
                    ->money('KWD')->sortable(),
                Tables\Columns\TextColumn::make('package')
                    ->label(__('package'))
                    ->state(fn($record) => $record->first_package() ?? ''),

                Tables\Columns\TextColumn::make('coupon')
                    ->label(__('coupon'))
                    ->state(fn($record) => $record->coupon ?? '-')
                    ->description(fn($record) => $record->discount ? formated_price($record->discount) : '')
                    ->money('KWD')
                    ->toggleable($isToggleHiddenByDefault = true),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label(__('payment status'))->sortable()
                    ->badge()
                    ->color(fn($record) => TapPaymentStatusEnum::from($record->payment_status)->getColor())
                    ->state(fn($record) => TapPaymentStatusEnum::from($record->payment_status)->getLabel())
                    ->translateLabel(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('created at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(TapPaymentStatusEnum::class),
            ])
            ->actions([

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
