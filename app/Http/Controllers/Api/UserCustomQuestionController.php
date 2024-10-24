<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\error;

class UserCustomQuestionController extends Controller
{
    public function index(Request $request)
    {
        $questions = Question::where('user_id', $request->user()->id)->get();
        return $this->success($questions);
    }

    public function show($id)
    {
        $question = Question::whereNotNull('user_id')->with('category')->findOrFail($id);
        if ($question->user_id !== auth('sanctum')->user()?->id) {
            return $this->error([], __('unauthorized'), 403);
        }
        return $this->success($question);
        // return $this->success(new QuestionResource($question));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'question'           => 'required|string',
                'answer'             => 'required|string',
                'level'              => 'nullable|sometimes|integer',
                'category_id'        => 'required|exists:categories,id',
            ]);
        } catch (ValidationException $e) {
            return $this->error($e->errors(), $e->getMessage(), 422);
        }

        $question = Question::firstOrCreate([
            'question'            => $request->question,
            'user_id'             => $request->user()->id,
        ], [
            // 'question_media_url'    => $request->answer_media_url,
            // 'answer_media_url'    => $request->answer_media_url,
            'answer'              => $request->answer,
            'level'               => $request->level ?? 1,
            'category_id'         => $request->category_id
        ]);

        return $this->success(new QuestionResource($question));
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'question'       => 'required|sometimes|string',
                'answer'         => 'required|sometimes|string',
            ]);
        } catch (ValidationException $e) {
            return $this->error($e->errors(), $e->getMessage(), 422);
        }

        $question = Question::findOrFail($id);

        // Ensure the user owns the category or has permission
        if ($question->user_id !== $request->user()->id) {
            return $this->error([], __('unauthorized'), 403);
        }

        $question->update($validated);

        return $this->success(new QuestionResource($question));
    }

    public function destroy(Request $request, $id)
    {
        try {
            $question = Question::findOrFail($id);
        } catch (\Throwable $th) {
            return $this->error([], __('not found'), 404);
        }

        // Ensure the user owns the question or has permission
        if ($question->user_id !== $request->user()->id) {
            return $this->error([], __('unauthorized'), 403);
        }
        $question->delete();

        return $this->success([], __('question deleted successfully'));
    }
}
