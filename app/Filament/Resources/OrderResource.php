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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // not finished yet

                // Select::make('user_id')
                //     ->relationship('user', 'name')
                //     ->required(),
                // TextInput::make('total')
                //     ->required()
                //     ->numeric()
                //     ->disabled(),
                // TextInput::make('discount')
                //     ->numeric(),
                // TextInput::make('credits')
                //     ->integer(),
                // TextInput::make('coupon'),
                // TextInput::make('tap_id')
                //     ->disabled(),
                // Select::make('payment_status')
                //     ->options(TapPaymentStatusEnum::class)
                //     ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->searchable(),
                Tables\Columns\TextColumn::make('total')->money('KWD')->sortable(),
                Tables\Columns\TextColumn::make('package')
                    ->state(fn($record) => $record->first_package() ?? ''),
                // Tables\Columns\TextColumn::make('discount')->money('KWD')
                //     ->toggleable($isToggleHiddenByDefault = true),
                Tables\Columns\TextColumn::make('coupon')
                    ->state(fn($record) => $record->coupon ?? '-')
                    ->description(fn($record) => $record->discount ? formated_price($record->discount) : '')
                    ->money('KWD')
                    ->toggleable($isToggleHiddenByDefault = true),
                Tables\Columns\TextColumn::make('payment_status')->sortable()
                    ->badge()
                    ->color(fn($record) => TapPaymentStatusEnum::from($record->payment_status)->getColor())
                    ->state(fn($record) => TapPaymentStatusEnum::from($record->payment_status)->getLabel())
                    ->translateLabel(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
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

    // public static function infolist(Infolist $infolist): Infolist
    // {
    //     return $infolist
    //         ->schema([
    //             Section::make('Order Details')
    //                 ->schema([
    //                     TextEntry::make('user.name')->label('Customer'),
    //                     TextEntry::make('total')->money('KWD'),
    //                     TextEntry::make('discount')->money('KWD'),
    //                     TextEntry::make('coupon'),
    //                     TextEntry::make('tap_id')->label('TAP ID'),
    //                     TextEntry::make('payment_status'),
    //                     TextEntry::make('created_at')->dateTime(),
    //                 ])->columns(3),
    //             Section::make('Packages')
    //                 ->schema([
    //                     RepeatableEntry::make('packages')
    //                         ->schema([
    //                             TextEntry::make('package.title')->label('Package'),
    //                             TextEntry::make('quantity'),
    //                             TextEntry::make('price')->money('KWD'),
    //                         ])
    //                         ->columns(3)
    //                 ])
    //         ]);
    // }

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
