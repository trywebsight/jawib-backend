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
                'title' => $data['title'],
                'selected_categories' => json_encode($selectedCategories),
            ]);

            $this->attachCategoriesToGame($game, $selectedCategories);
            $this->attachQuestionsToGame($game, $selectedCategories, $user);

            $user->withdraw(1);

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
                    ->whereNotIn('id', function ($query) use ($user) {
                        $query->select('question_id')
                            ->from('game_questions')
                            ->join('games', 'games.id', '=', 'game_questions.game_id')
                            ->where('games.user_id', $user->id);
                    })
                    ->inRandomOrder()
                    ->take($questionsPerLevel)
                    ->get();

                if ($questions->count() < $questionsPerLevel) {
                    throw new \Exception("Not enough unique questions for category {$category->title} at level {$level}");
                }

                $game->questions()->attach($questions->pluck('id'));
            }
        }
    }
}
