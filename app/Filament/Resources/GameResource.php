<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameResource\Pages;
use App\Models\Game;
use App\Models\Category;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class GameResource extends Resource
{
    protected static ?string $model = Game::class;

    protected static ?string $navigationIcon = 'heroicon-c-play-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Repeater::make('teams')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Team Name')
                            ->required(),
                    ])
                    ->minItems(1)
                    ->maxItems(4)
                    ->required(),
                // Forms\Components\Repeater::make('teams')
                //     ->separator(',')
                //     ->default([])
                //     // ->minItems(2)
                //     // ->maxItems(4)
                //     ->rules(['array', 'min:2', 'max:4'])
                //     ->required(),
                Forms\Components\Select::make('categories')
                    ->multiple()
                    ->options(Category::pluck('title', 'id'))
                    ->required()
                    ->minItems(4)
                    ->maxItems(6),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('teams')
                    ->label(__('teams'))
                    ->getStateUsing(function ($record) {
                        return $record->teams();
                    })
                    ->badge()
                    ->color('info')
                    ->listWithLineBreaks(),
                Tables\Columns\TextColumn::make('categories.title')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListGames::route('/'),
            'create' => Pages\CreateGame::route('/create'),
            'edit' => Pages\EditGame::route('/{record}/edit'),
            'view' => Pages\ViewGame::route('/{record}'),
        ];
    }
}
