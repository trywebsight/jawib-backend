<?php

namespace App\Filament\Resources\Store\StoreOrderResource\Pages;

use App\Enums\OrderStatusEnum;
use App\Enums\StoreOrderStatusEnum;
use App\Enums\TapPaymentStatusEnum;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Filament\Resources\UserResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Components\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\HtmlString;

class ViewStoreOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return self::staticInfolist($infolist);
    }

    public static function staticInfolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist->schema([

            Infolists\Components\Group::make()

                ->columnSpan(2)
                ->schema([

                    Infolists\Components\Section::make()
                        ->columns()
                        ->schema([

                            Infolists\Components\TextEntry::make('name')
                                ->label('Name')
                                ->url(
                                    fn(Order $record) => $record->user_id ? UserResource::getUrl('edit', [$record->user_id]) : "#"
                                ),
                            Infolists\Components\TextEntry::make('phone')
                                ->label('phone'),

                            Infolists\Components\TextEntry::make('payment_status')
                                ->suffixAction(
                                    Action::make('update_payment_status')
                                        ->icon('heroicon-o-pencil')
                                        ->button()
                                        ->slideOver()
                                        ->form(fn(Order $record) => [
                                            Forms\Components\ToggleButtons::make('payment_status')
                                                ->default($record->payment_status)
                                                ->inline()
                                                ->options(TapPaymentStatusEnum::class)
                                                ->enum(TapPaymentStatusEnum::class)
                                                ->required(),
                                        ])
                                        ->successNotificationTitle(
                                            __('Payment status updated successfully!')
                                        )
                                        ->action(function (Action $action, Order $record, array $data): void {
                                            $record->update(['payment_status' => $data['payment_status']]);

                                            $action->success();
                                        }),
                                ),

                            Infolists\Components\TextEntry::make('status')
                                ->label('Order status')
                                ->suffixAction(
                                    Action::make('update_status')
                                        ->icon('heroicon-o-pencil')
                                        ->button()
                                        ->slideOver()
                                        ->form(fn(Order $record) => [
                                            Forms\Components\ToggleButtons::make('status')
                                                ->default($record->status)
                                                ->inline()
                                                ->options(StoreOrderStatusEnum::class)
                                                ->enum(StoreOrderStatusEnum::class)
                                                ->required(),
                                        ])
                                        ->successNotificationTitle(
                                            __('Status updated successfully!')
                                        )
                                        ->action(function (Action $action, Order $record, array $data): void {
                                            $record->update(['status' => $data['status']]);

                                            $action->success();
                                        })
                                ),
                        ]),
                    // Address
                    Infolists\Components\Section::make('Address')
                        ->schema([
                            Infolists\Components\TextEntry::make('address.building_type')
                                ->label('Building type'),
                            Infolists\Components\TextEntry::make('address.governorate')
                                ->label('Governorate'),
                            Infolists\Components\TextEntry::make('address.area')
                                ->label('Area'),
                            Infolists\Components\TextEntry::make('address.block')
                                ->label('Block'),
                            Infolists\Components\TextEntry::make('address.avenue')
                                ->label('Avenue'),
                            Infolists\Components\TextEntry::make('address.street')
                                ->label('Street'),
                            Infolists\Components\TextEntry::make('address.floor')
                                ->label('Floor'),
                            Infolists\Components\TextEntry::make('address.house')
                                ->label('House'),
                            Infolists\Components\TextEntry::make('guest.address.comment')
                                ->columnSpanFull()
                                ->label('Address Comment'),
                        ])
                        ->columns(4)
                        ->visible(fn(Order $record) => !$record->is_guest),

                    // New Order Items Section
                    Infolists\Components\Section::make('Order Items')
                        ->schema([
                            Infolists\Components\RepeatableEntry::make('items')
                                ->schema([
                                    Infolists\Components\TextEntry::make('product.title_en')
                                        ->columnSpan(3)
                                        ->label('Product Name'),
                                    Infolists\Components\TextEntry::make('quantity')
                                        ->label('Quantity'),
                                    Infolists\Components\TextEntry::make('price')
                                        ->label('Unit Price')
                                        ->money('KWD'),
                                    Infolists\Components\TextEntry::make('total')
                                        ->label('Total')
                                        ->money('KWD')
                                        ->state(fn($record) => $record->price * $record->quantity),
                                ])
                                ->columns(6)
                        ]),
                ]),

            Infolists\Components\Group::make()
                ->columnSpan(1)
                ->schema([
                    Infolists\Components\Section::make()
                        ->schema([

                            Infolists\Components\TextEntry::make('delivery_fee')
                                ->icon('heroicon-s-currency-dollar')
                                ->money('KWD'),
                            Infolists\Components\TextEntry::make('discount')
                                ->icon('heroicon-s-currency-dollar')
                                // ->state(fn(Order $record) => $record->total)
                                ->money('KWD'),
                            Infolists\Components\TextEntry::make('total')
                                ->icon('heroicon-s-currency-dollar')
                                ->state(fn(Order $record) => $record->total)
                                ->money('KWD'),

                        ]),
                    Infolists\Components\Section::make()
                        ->schema([

                            Infolists\Components\TextEntry::make('created_at')
                                ->dateTime()
                                ->icon('heroicon-s-calendar'),

                            Infolists\Components\TextEntry::make('updated_at')
                                ->dateTime()
                                ->icon('heroicon-s-calendar'),

                            // Infolists\Components\TextEntry::make('deleted_at')
                            //                                 //     ->dateTime()
                            //     ->icon('heroicon-s-calendar'),
                        ]),
                ]),
        ])
            ->columns(3);
    }
}
