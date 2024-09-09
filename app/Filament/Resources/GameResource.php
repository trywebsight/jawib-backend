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
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('categories')
                    ->multiple()
                    ->options(Category::pluck('title', 'id'))
                    ->required()
                    ->minItems(6)
                    ->maxItems(6)
                    ->disabledOn('edit')
                    ->afterStateUpdated(function ($state, callable $set, $livewire) {
                        if (count($state) === 6) {
                            $userId = $livewire->data['user_id'] ?? null;
                            $questions = self::getQuestionsForCategories($state, $userId);
                            $set('questions', $questions->pluck('id')->toArray());
                        }
                    }),
                // ->afterStateUpdated(function ($state, callable $set) {
                //     if (count($state) === 6) {
                //         $questions = self::getQuestionsForCategories($state);
                //         $set('questions', $questions->pluck('id')->toArray());
                //     }
                // }),
                Forms\Components\Hidden::make('questions'),
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            // 'edit' => Pages\EditGame::route('/{record}/edit'),
            'view' => Pages\ViewGame::route('/{record}'),
        ];
    }

    protected static function getQuestionsForCategories($categoryIds, $userId)
    {
        $questions = collect();
        $usedQuestionIds = Game::where('user_id', $userId)
            ->with('questions')
            ->get()
            ->pluck('questions')
            ->flatten()
            ->pluck('id')
            ->unique();

        foreach ($categoryIds as $categoryId) {
            for ($level = 1; $level <= 3; $level++) {
                $levelQuestions = Question::where('category_id', $categoryId)
                    ->where('level', $level)
                    ->whereNotIn('id', $usedQuestionIds)
                    ->whereNotIn('id', $questions->pluck('id'))
                    ->inRandomOrder()
                    ->take(2)
                    ->get();

                $questions = $questions->concat($levelQuestions);
                $usedQuestionIds = $usedQuestionIds->concat($levelQuestions->pluck('id'));
            }
        }

        return $questions;
    }
}
