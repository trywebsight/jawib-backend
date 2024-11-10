<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\TapPaymentStatusEnum;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\UserResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Components\Actions\Action;
use Filament\Forms;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->url(static::getResource()::getUrl()) // or you can use url(static::getResource()::getUrl())
                ->button()
                ->color('info'),
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
                            Infolists\Components\TextEntry::make('user.name')
                                ->url(
                                    fn(Order $record) => $record->user_id ? UserResource::getUrl('edit', [$record->user_id]) : "#"
                                )
                                ->label(__('name')),
                            Infolists\Components\TextEntry::make('user.username')
                                ->label(__('username')),
                            Infolists\Components\TextEntry::make('user.phone')
                                ->label(__('phone')),
                            Infolists\Components\TextEntry::make('user.email')
                                ->label(__('email')),
                            Infolists\Components\TextEntry::make('user.country')
                                ->label(__('country')),
                            Infolists\Components\TextEntry::make('user.dob')
                                ->label(__('birth of date')),
                            // Infolists\Components\TextEntry::make('')
                            //     ->label(__('')),



                            // Infolists\Components\TextEntry::make('status')
                            //     ->label('Order status')
                            //     ->suffixAction(
                            //         Action::make('update_status')
                            //             ->icon('heroicon-o-pencil')
                            //             ->button()
                            //             ->slideOver()
                            //             ->form(fn(Order $record) => [
                            //                 Forms\Components\ToggleButtons::make('status')
                            //                     ->default($record->status)
                            //                     ->inline()
                            //                     ->options(OrderStatusEnum::class)
                            //                     ->enum(OrderStatusEnum::class)
                            //                     ->required(),
                            //             ])
                            //             ->successNotificationTitle(
                            //                 __('Status updated successfully!')
                            //             )
                            //             ->action(function (Action $action, Order $record, array $data): void {
                            //                 $record->update(['status' => $data['status']]);

                            //                 $action->success();
                            //             })
                            //     ),
                        ]),
                    // New Order Items Section
                    Infolists\Components\RepeatableEntry::make('packages')
                        ->label(__(''))
                        ->schema([
                            Infolists\Components\TextEntry::make('package.title')
                                ->columnSpan(3)
                                ->label(__('package title')),
                            // Infolists\Components\TextEntry::make('quantity')
                            //     ->label('Quantity'),
                            Infolists\Components\TextEntry::make('price')
                                ->label(__('package price'))
                                ->money('KWD'),
                            // Infolists\Components\TextEntry::make('total')
                            //     ->label('Total')
                            //     ->money('KWD')
                            //     ->state(fn($record) => $record->price * $record->quantity),
                        ])
                        ->columns(4),
                ]),

            // Right sidebar
            Infolists\Components\Group::make()
                ->columnSpan(1)
                ->schema([
                    Infolists\Components\Section::make()
                        ->schema([

                            Infolists\Components\TextEntry::make('coupon')
                                ->icon('heroicon-o-tag')
                                ->visible(fn($record) => $record->coupon ?? false)
                                ->money('KWD'),
                            Infolists\Components\TextEntry::make('discount')
                                ->icon('heroicon-s-currency-dollar')
                                ->visible(fn($record) => $record->discount ?? false)
                                ->money('KWD'),
                            Infolists\Components\TextEntry::make('total')
                                ->icon('heroicon-s-currency-dollar')
                                ->state(fn(Order $record) => $record->total)
                                ->money('KWD'),

                            Infolists\Components\TextEntry::make('tap_id')
                                ->label(__('tap id')),
                            Infolists\Components\TextEntry::make('payment_status')
                                ->badge()
                                ->color(fn($record) => TapPaymentStatusEnum::from($record->payment_status)->getColor())
                                ->state(fn($record) => TapPaymentStatusEnum::from($record->payment_status)->getLabel())
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
