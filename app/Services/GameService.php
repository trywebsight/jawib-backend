<?php

namespace App\Services;

use App\Models\Game;
use App\Models\User;
use App\Models\Question;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GameService
{
    public function createGame(User $user, array $data)
    {
        if ($user->balance < 1) {
            throw new \Exception(__('insufficient balance'));
        }

        $selectedCategories = $data['categories'];
        if (count($selectedCategories) < 4 || count($selectedCategories) > 6) {
            throw new \Exception(__('you must select 4 to 6 categories'));
        }

        DB::beginTransaction();
        try {
            $game = Game::create([
                'user_id' => $user->id,
                'teams' => $data['teams'],
                'title' => $data['title'],
                'selected_categories' => json_encode($selectedCategories),
            ]);

            $this->attachCategoriesToGame($game, $selectedCategories);
            $this->attachQuestionsToGame($game, $selectedCategories, $user);

            $user->withdraw(1, ['description' => 'game creation #' . $game->id, 'game_id' => $game->id]);
            // $user->withdraw(1);

            DB::commit();
            return $game;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Game creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function attachCategoriesToGame(Game $game, array $categoryIds)
    {
        $game->categories()->attach($categoryIds);
    }

    private function attachQuestionsToGame(Game $game, array $categoryIds, User $user)
    {
        $questionsPerCategory = 6;
        $questionsPerLevel = 2;

        foreach ($categoryIds as $categoryId) {
            $category = Category::findOrFail($categoryId);

            for ($level = 1; $level <= 3; $level++) {
                $questions = Question::where('category_id', $categoryId)
                    ->where('level', $level)
                    ->inRandomOrder()
                    ->take($questionsPerLevel)
                    ->get();

                // if ($questions->count() < $questionsPerLevel) {
                // throw new \Exception("Not enough unique questions for category {$category->title} at level {$level}");
                // }

                $game->questions()->attach($questions->pluck('id'));
            }
        }
    }

    public function getGame(Game $game)
    {
        $levels = [
            1 => __('200'),
            2 => __('400'),
            3 => __('600')
        ];

        $categories = $game->categories->map(function ($category) use ($game, $levels) {
            // Dynamically build questions by levels
            $questionsByLevel = [];

            foreach ($levels as $level => $label) {
                $questionsByLevel[$label] = $game->questions
                    ->where('category_id', $category->id)
                    ->where('level', $level)
                    ->map(function ($question) {
                        return [
                            'id' => $question->id,
                            'question' => $question->question,
                            'question_media_url' => $question->question_media_url,
                            'question_media_type' => $question->question_media_type,
                            'answer' => $question->answer,
                            'answer_media_url' => $question->answer_media_url,
                            'answer_media_type' => $question->answer_media_type,
                            'level' => $question->level,
                            'diff' => $question->diff,
                            'options' => $question->options,
                        ];
                    })
                    ->values();
            }

            return [
                'id' => $category->id,
                'title' => $category->title,
                'image' => $category->image,
                'questions' => $questionsByLevel,
            ];
        });

        $data = [
            'id' => $game->id,
            'title' => $game->title,
            'teams' => $game->teams,
            'categories' => $categories,
        ];

        return $data;
    }

    public function gamesHistory(User $user)
    {
        try {
            $games = $user->games->map(function ($game) {
                return [
                    'id' => $game->id,
                    'title' => $game->title,
                    'selected_categories' => $game->categories->map(function ($cat) {
                        return [
                            'id' => $cat->id,
                            'title' => $cat->title,
                            'image' => $cat->image,
                        ];
                    })
                ];
            });

            return $games;
        } catch (\Throwable $th) {
            logger()->error('error in getting user gamesHistory: ', [$th->getMessage()]);
            return [];
        }
    }
}
