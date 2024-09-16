<?php

namespace App\Filament\Resources;

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
                    ->label(__('max uses per user'))
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required(),
                Forms\Components\TextInput::make('max_users')
                    ->label(__('max users'))
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->helperText(__('leave empty for unlimited users')),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label(__('expiration date'))
                    ->nullable()
                    ->helperText(__('leave empty for no expiration')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('coupon code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_uses_per_user')
                    ->label(__('max uses/user'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_users')
                    ->label(__('max users'))
                    ->sortable()
                    ->default(__('unlimited'))
                    ->formatStateUsing(fn($state) => $state ?? __('unlimited')),
                Tables\Columns\TextColumn::make('total_uses')
                    ->label(__('total uses'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label(__('expires at'))
                    ->sortable()
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
