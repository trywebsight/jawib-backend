<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\QuestionFeedback;

class QuestionFeedbackController extends Controller
{
    public function feedback(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'game_id' => 'nullable|exists:games,id', // Assuming there's a `games` table
            'question_id' => 'required|exists:questions,id', // Ensure the question exists
            'feedback' => 'required|string|min:5|max:255', // Feedback must be at least 5 characters long
        ]);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return $this->error($validator->errors(), __("validation failed"));
        }

        // Store the feedback
        $feedback = QuestionFeedback::firstOrCreate([
            'user_id'       => auth('sanctum')->id(),
            'question_id'   => $request->question_id,
            'feedback'      => $request->feedback,
        ]);

        // Return a success response
        return $this->success($feedback, __('feedback submitted successfully'), 201);
    }
}
