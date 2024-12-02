<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SuggestedQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
    // public function store(Request $request)
    // {
    //     Log::channel('suggested-question')->info('Received request data:', [
    //         'isJson' => $request->isJson(),
    //         'all' => $request->all(),
    //         'files' => $request->allFiles(),
    //         'headers' => $request->headers->all(),
    //         'content-type' => $request->header('Content-Type'),
    //     ]);

    //     if ($request->isJson()) {
    //         return $this->error('Invalid Content-Type. Use multipart/form-data for file uploads.', 415);
    //     }

    //     try {
    //         $validatedData = $request->validate([
    //             'question' => 'required|string',
    //             'category_id' => [
    //                 'nullable',
    //                 Rule::exists('categories', 'id')->whereNull('user_id'),
    //             ],
    //             'answer' => 'nullable|string',
    //             'images' => 'nullable|array',
    //             'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //         ]);
    //     } catch (ValidationException $e) {
    //         return $this->error($e->errors(), $e->getMessage(), 422);
    //     }


    //     $suggestedQuestion = SuggestedQuestion::firstOrCreate([
    //         'question'    => $request->question,
    //         'user_id'     => $request->user()->id,
    //     ], [
    //         'category_id' => $request->category_id,
    //         'answer'      => $request->answer,
    //         // 'images'      => $imagePaths,
    //     ]);

    //     if (empty($suggestedQuestion->images)) {
    //         // Handle image uploads
    //         $imagePaths = [];
    //         if ($request->hasFile('images')) {
    //             foreach ($request->file('images') as $image) {
    //                 // Store the image on the 'do' disk in the 'suggested-questions' folder
    //                 $path = $image->store('suggested-questions', 'do');
    //                 // Generate a URL for the stored image
    //                 $url = Storage::disk('do')->url($path);
    //                 $imagePaths[] = $url;
    //             }
    //         }
    //         $suggestedQuestion->images = $imagePaths;
    //         $suggestedQuestion->save();
    //     }

    //     return $this->success($suggestedQuestion, __('thank you for your suggestion'), 201);
    // }
    public function store(Request $request)
    {
        Log::channel('suggested-question')->info('Received request data:', [
            'isJson' => $request->isJson(),
            'request->all' => $request->all(),
            'headers' => $request->headers->all(),
        ]);
        try {
            $validatedData = $request->validate([
                'question' => 'required|string',
                'category_id' => [
                    'nullable',
                    Rule::exists('categories', 'id')->whereNull('user_id'),
                ],
                'answer' => 'nullable|string',
                'images' => 'nullable|array',
                'images.*' => 'string|regex:/^data:image\/[a-zA-Z]+;base64,/', // Validate base64 images
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
        ]);

        if (empty($suggestedQuestion->images) && $request->has('images')) {
            $imagePaths = [];
            foreach ($request->images as $base64Image) {
                // Remove data:image/jpeg;base64, from the base64 string
                $image_parts = explode(";base64,", $base64Image);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);

                // Generate unique filename
                $filename = uniqid() . '.' . $image_type;

                // Store the file
                $path = 'suggested-questions/' . $filename;
                Storage::disk('do')->put($path, $image_base64);

                // Generate URL
                $url = Storage::disk('do')->url($path);
                $imagePaths[] = $url;
            }

            $suggestedQuestion->images = $imagePaths;
            $suggestedQuestion->save();
        }

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
