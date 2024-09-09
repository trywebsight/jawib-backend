<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

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
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('categories.title')
                    ->listWithLineBreaks()
                    ->limitList(6),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, string $model): Model {
                        // Ensure 'categories' key exists
                        $categories = $data['categories'] ?? [];

                        if (empty($categories)) {
                            throw new \Exception('No categories selected.');
                        }

                        $game = $model::create($data);
                        $game->categories()->sync($categories);
                        return $game;
                    })
                    ->after(function (Model $record, array $data) {
                        $user = $record->user;
                        $categoryIds = $data['categories'];

                        foreach ($categoryIds as $categoryId) {
                            $usedQuestionIds = $user->games()
                                ->whereHas('categories', function ($query) use ($categoryId) {
                                    $query->where('categories.id', $categoryId);
                                })
                                ->whereHas('questions', function ($query) use ($categoryId) {
                                    $query->where('category_id', $categoryId);
                                })
                                ->with('questions:id') // Ensure the correct table and column are referenced
                                ->pluck('questions.id') // Correctly pluck the question IDs
                                ->toArray();

                            $questions = Question::where('category_id', $categoryId)
                                ->whereNotIn('id', $usedQuestionIds)
                                ->inRandomOrder()
                                ->get()
                                ->groupBy('level')
                                ->map(function ($group) {
                                    return $group->take(2);
                                })
                                ->flatten();

                            // If we don't have enough unique questions, reuse some
                            if ($questions->count() < 6) {
                                $additionalQuestions = Question::where('category_id', $categoryId)
                                    ->whereIn('id', $usedQuestionIds)
                                    ->inRandomOrder()
                                    ->take(6 - $questions->count())
                                    ->get();
                                $questions = $questions->concat($additionalQuestions);
                            }

                            $record->questions()->attach($questions->pluck('id')->toArray());
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
