<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
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
        return $this->success($request, __(''));
        $game = Game::find($id);
        if (!$game) {
            return $this->error([], __("invalid game id"), 422);
        }
        return $this->success($game, __('games'));
    }
}
