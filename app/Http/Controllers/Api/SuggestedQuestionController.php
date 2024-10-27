<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SuggestedQuestion;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class SuggestedQuestionController extends Controller
{
    // Display a listing of the resource.
    public function index(Request $request)
    {
        $suggestedQuestions = SuggestedQuestion::where('user_id', $request->user()->id)->get();
        return $this->success($suggestedQuestions, __('suggested questions'));
    }


    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'question' => 'required|string',
                'category_id' => [
                    'nullable',
                    Rule::exists('categories', 'id')->whereNull('user_id'),
                ],
                'answer' => 'nullable|string',
                'images' => 'nullable|array',
                // 'images.*' => 'file', // Assuming images are stored as URLs or paths
            ]);
        } catch (ValidationException $e) {
            return $this->error($e->errors(), $e->getMessage(), 422);
        }

        $suggestedQuestion = SuggestedQuestion::firstOrCreate([
            'question'    => $request->question,
            'user_id'     => $request->user()->id,
        ], [
            'category_id' => $request->category_id,
            'answer'      => $request->answer,
            'images'      => [],
        ]);
        return $this->success($suggestedQuestion, __('thank you for your suggestion'), 201);
    }

    // Display the specified resource.
    public function show($id)
    {
        $suggestedQuestion = SuggestedQuestion::whereNotNull('user_id')->with('category')->findOrFail($id);
        if ($suggestedQuestion->user_id !== auth('sanctum')->user()?->id) {
            return $this->error([], __('unauthorized'), 403);
        }
        return $this->success($suggestedQuestion);
    }

    // Update the specified resource in storage.
    public function update($id, Request $request)
    {
        try {
            $validatedData = $request->validate([
                'question' => 'required|sometimes|string',
                'answer' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return $this->error($e->errors(), $e->getMessage(), 422);
        }
        $suggestedQuestion = SuggestedQuestion::findOrFail($id);

        // Ensure the user owns the category or has permission
        if ($suggestedQuestion->user_id !== $request->user()->id) {
            return $this->error([], __('unauthorized'), 403);
        }

        $suggestedQuestion->update($validatedData);
        return $this->success($suggestedQuestion, __('suggestion updated successfully'));
    }

    // Remove the specified resource from storage.
    public function destroy(Request $request, $id)
    {

        try {
            $suggestedQuestion = SuggestedQuestion::findOrFail($id);
        } catch (\Throwable $th) {
            return $this->error([], __('not found'), 404);
        }

        // Ensure the user owns the question or has permission
        if ($suggestedQuestion->user_id !== $request->user()->id) {
            return $this->error([], __('unauthorized'), 403);
        }

        $suggestedQuestion->delete();

        return $this->success([], __('suggesetion deleted successfully'));
    }
}
