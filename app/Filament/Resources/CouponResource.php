<?php

namespace App\Filament\Resources;

use App\Enums\CouponTypeEnum;
use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label(__('coupon code'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->suffixIconColor('blue')
                    ->suffixAction(
                        Action::make('copyCostToPrice')
                            ->icon('heroicon-c-sparkles')
                            ->action(function (Set $set, $state) {
                                do {
                                    $randomCode = strtoupper(Str::random(8));
                                    $exists = Coupon::where('code', $randomCode)->exists();
                                } while ($exists);

                                $set('code', $randomCode);
                            })
                    ),
                Forms\Components\TextInput::make('max_uses_per_user')
                    ->label(__('max uses / per user'))
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required(),
                Forms\Components\TextInput::make('max_uses')
                    ->label(__('total max uses'))
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->helperText(__('leave empty for unlimited uses')),

                Forms\Components\ToggleButtons::make('discount_type')
                    ->label(__('discount type'))
                    ->inline()
                    ->inlineLabel(false)
                    ->options(CouponTypeEnum::class)
                    ->enum(CouponTypeEnum::class)
                    ->icons([
                        'fixed' => 'heroicon-s-currency-dollar',
                        'percent' => 'heroicon-c-percent-badge',
                    ])
                    ->live()
                    ->afterStateUpdated(fn(Set $set) => $set('discount_value', null))
                    ->required(),

                Forms\Components\TextInput::make('discount_value')
                    ->label(__('discount value'))
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->prefix(function (Forms\Get $get) {
                        return $get('discount_type') === 'fixed' ? 'KWD' : '';
                    })
                    ->suffix(function (Forms\Get $get) {
                        return $get('discount_type') === 'percent' ? '%' : '';
                    })
                    ->maxValue(fn(Forms\Get $get) => $get('discount_type') === 'percent' ? 100 : null),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label(__('expiration date'))
                    ->nullable()
                    ->helperText(__('leave empty for no expiration')),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('coupon code'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Coupon copied')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_value')
                    ->label(__('discount value'))
                    ->sortable()
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $record->discount_type === 'percent' ? "{$state}%" : "KWD ${state}"
                    ),
                Tables\Columns\TextColumn::make('max_uses_per_user')
                    ->label(__('max uses/user'))
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_uses')
                    ->label(__('max users'))
                    ->sortable()
                    ->toggleable()
                    ->default(__('unlimited'))
                    ->formatStateUsing(fn($state) => $state ?? __('unlimited')),
                    Tables\Columns\TextColumn::make('total_uses')
                    ->label(__('total uses'))
                    ->toggleable()
                    ->default(0)
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ?? 0),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label(__('expires at'))
                    ->sortable()
                    ->toggleable()
                    ->dateTime('M d, Y'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label(__('active'))
                    ->getStateUsing(fn($record) => $record->isActive()),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('created at'))
                    ->sortable()
                    ->toggleable($isToggleHiddenByDefault = true)
                    ->dateTime('M d, Y H:i'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Optionally add a Delete action
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Define any relations if necessary
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            // 'create' => Pages\CreateCoupon::route('/create'),
            // 'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
