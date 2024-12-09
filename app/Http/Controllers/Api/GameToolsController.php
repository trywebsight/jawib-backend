<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GameToolsController extends Controller
{
    protected $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    public function joker(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'game_id'            => 'required|integer|exists:games,id',
            'question_id'        => 'required|integer|exists:questions,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), __("validation failed"));
        }

        $user = auth('sanctum')->user();
        $game = $user->games->find($request->game_id);
        if (!$game) {
            return $this->error([], __("game not found"), 404);
        }
        $joker = $this->gameService->joker($game, $request->question_id);
        return $this->success($joker);
    }
}
