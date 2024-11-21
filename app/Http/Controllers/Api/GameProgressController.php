<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameProgress;
use Illuminate\Http\Request;

class GameProgressController extends Controller
{
    // GET: Retrieve game progress by game ID
    public function show($id)
    {
        $progress = GameProgress::where('game_id', $id)->first();

        if (!$progress) {
            return $this->error([], __('no progress found for this game'), 404);
        }

        return $this->success($progress);
    }

    // POST: Save or update game progress
    public function store(Request $request)
    {
        $request->validate([
            'game_id'   => 'required|integer|exists:games,id',
            'data'      => 'required|array',
        ]);

        $progress = GameProgress::updateOrCreate(
            ['game_id' => $request->input('game_id')],
            ['data' => $request->input('data')]
        );

        return $this->success($progress, __('game progress saved successfully'));
    }
}
