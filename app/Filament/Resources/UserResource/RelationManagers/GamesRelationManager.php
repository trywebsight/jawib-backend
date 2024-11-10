<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\GameResource;
use App\Models\Category;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GamesRelationManager extends RelationManager
{
    protected static string $relationship = 'games';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => $this->ownerRecord->id),
                Forms\Components\Select::make('categories')
                    ->label('Categories')
                    ->multiple()
                    ->options(fn() => Category::all()->pluck('title', 'id'))
                    ->required()
                    ->helperText('Select up to 6 categories.')
                    ->rules(['required', 'array', 'max:6'])
                    ->preload(),


            ]);
    }

    public function table(Table $table): Table
    {
        // return (new GameResource)->table($table);
        return $table
            ->recordTitleAttribute('title')
            ->columns([
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
            ->headerActions([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
