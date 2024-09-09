<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\GamesRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\TransactionsRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-m-users';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('customers');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label(__('name'))->autofocus()->required(),
                TextInput::make('email')->label(__('email'))->email()->nullable(),
                TextInput::make('phone')->label(__('phone'))->numeric()->required(),
                // Forms\Components\TextInput::make('game_credits')
                //     ->numeric()
                //     ->required(),
                TextInput::make('password')->label(__('password'))->password()->revealable()
                    ->dehydrateStateUsing(fn($state) => bcrypt($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create'),

                Toggle::make('is_active')->label(__('is active'))->default(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('name')),
                TextColumn::make('email')->label(__('email')),
                TextColumn::make('phone')->label(__('phone')),
                TextColumn::make('game_credits')->label(__('game credits')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('send_credit_to_user')
                    ->label('credit')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('gray')
                    ->form([
                        TextInput::make('game_credits')
                            ->label(__('Game credits amount'))
                            ->inputMode('numeric') // Set input mode to numeric
                            ->integer()
                            ->default(fn($record) => $record->game_credits)
                            ->required()
                            ->placeholder('Please provide the amount you want to credit this user.'),
                    ])
                    ->action(function (array $data, $record) {
                        // Update user's game credits
                        $user = User::find($record->id); // Find the user by record ID
                        $user->game_credits = $data['game_credits']; // Add the credits to the existing amount
                        $user->save(); // Save the updated game credits

                        Notification::make()
                            ->title(__('user games credit updated'))
                            ->success()
                            ->send();
                    }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TransactionsRelationManager::class,
            GamesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
