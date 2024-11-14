<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\CategoriesRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\GamesRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\QuestionsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\TransactionsRelationManager;
use App\Jobs\UserGetCreditJob;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-m-users';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('users');
    }

    public static function getNavigationLabel(): string
    {
        return self::getPluralModelLabel();
    }

    public static function getPluralLabel(): string
    {
        return self::getPluralModelLabel();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // name Field
                TextInput::make('name')->label(__('name'))->autofocus()->required(),
                // email Field
                TextInput::make('email')->label(__('email'))->email()->nullable(),
                // phone Field
                TextInput::make('phone')->label(__('phone'))->numeric()->required(),
                // Username Field
                TextInput::make('username')
                    ->label(__('username'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->rules(['required', 'string', 'max:255']),
                // Date of Birth Field
                DatePicker::make('dob')
                    ->label(__('date of birth'))
                    ->nullable(),
                // Gender Field
                Radio::make('gender')
                    ->label(__('gender'))
                    ->inline()
                    ->inlineLabel(false)
                    ->options([
                        'male' => __('male'),
                        'female' => __('female'),
                    ])
                    ->required(),
                // Country Field
                Select::make('country')
                    ->label(__('country'))
                    ->options([
                        'Kuwait' => __('kuwait'),
                        'Saudi Arabia' => __('saudi arabia'),
                        'United Arab Emirates' => __('united arab emirates'),
                        'Qatar' => __('qatar'),
                        'Oman' => __('oman'),
                        'Bahrain' => __('bahrain'),
                    ])
                    ->required(),
                // Points Field
                TextInput::make('points')
                    ->label(__('points'))
                    ->numeric()
                    ->default(0)
                    ->required(),
                // Password
                TextInput::make('password')->label(__('password'))->password()->revealable()
                    ->dehydrateStateUsing(fn($state) => bcrypt($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->columnSpanFull()
                    ->required(fn(string $context): bool => $context === 'create'),
                // Phone Verification Toggle
                Toggle::make('phone_verified')
                    ->label(__('phone verified'))
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('name')),
                TextColumn::make('username')->label(__('username')),
                TextColumn::make('email')->label(__('email')),
                TextColumn::make('phone')->label(__('phone')),
                TextColumn::make('country')->label(__('country')),
                IconColumn::make('phone_verified')
                    ->label(__('verified'))
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('rank')
                    ->label(__('level'))
                    ->sortable()
                    ->badge()
                    ->getStateUsing(fn($record) => "{$record->rank} - ({$record->points})")
                    ->color('primary'),
                TextColumn::make('balance')->label(__('game credits'))
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('send_credit_to_user')
                    ->label('add credit')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('gray')
                    ->form([
                        TextInput::make('amount')
                            ->label(__('credits amount'))
                            ->inputMode('numeric') // Set input mode to numeric
                            ->integer()
                            ->minValue(0)
                            // ->default(fn($record) => $record->credits)
                            ->required()
                            ->placeholder('5'),
                    ])
                    ->action(function (array $data, $record) {
                        $user = $record;

                        $user->deposit($data['amount'], ['description' => 'credit added by admin']);

                        UserGetCreditJob::dispatch($user);

                        Notification::make()
                            ->title(__('user games credit updated'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TransactionsRelationManager::class,
            GamesRelationManager::class,
            CategoriesRelationManager::class,
            QuestionsRelationManager::class,
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
