<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
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
        $games = $this->gameService->gamesHistory($user);
        return $this->success($games, __('games'));
    }
    function get_game($id)
    {
        $user = auth('sanctum')->user();
        $game = Game::find($id);

        // Ensure the game exists and belongs to the authenticated user
        if (!$game || $game->user_id != $user->id) {
            return $this->error([], __("invalid game id"), 422);
        }

        // Format the data
        $data = $this->gameService->getGame($game);

        return $this->success($data, __('games'));
    }

    function create_game(Request $request)
    {

        $request->validate([
            'title'         => 'required|string|max:255',
            'teams'         => 'sometimes|array|min:2|max:4',
            'categories'    => 'required|array|min:4|max:6',
            'categories.*'  => 'exists:categories,id',
        ]);
        $user = auth('sanctum')->user();
        try {
            $game = $this->gameService->createGame($user, $request->all());
            return $this->success(['game' => $game], __('game created successfully'));
        } catch (\Exception $e) {
            return $this->error(['errors' => [$e->getMessage()]], $e->getMessage(), 400);
        }
    }
}
