<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\Request;

class GameController extends Controller
{
    protected $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    function my_games(Request $request)
    {
        $user = auth('sanctum')->user();
        $games = $user->games;
        return $this->success($games, __('games'));
    }
    function get_game($id)
    {
        $user = auth('sanctum')->user();
        $game = Game::find($id);
        // make sure game is exist
        if (!$game || $game->user_id != $user->id) {
            return $this->error([], __("invalid game id"), 422);
        }
        return $this->success($game, __('games'));
    }
    function create_game(Request $request)
    {

        $request->validate([
            'title' => 'required|string|max:255',
            'categories' => 'required|array|min:4|max:6',
            'categories.*' => 'exists:categories,id',
        ]);
        $user = auth('sanctum')->user();
        try {
            $game = $this->gameService->createGame($user, $request->all());
            return $this->success(['game' => $game], __('Game created successfully'));
        } catch (\Exception $e) {
            return $this->error(['errors' => [$e->getMessage()]], $e->getMessage(), 400);
        }
    }
}
