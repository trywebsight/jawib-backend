<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GameController extends Controller
{
    function myGames(Request $request)
    {
        $user = auth('sanctum')->user();
        $games = $user->games;
        return $this->success($games, __('games'));
    }
}
