<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserCustomCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('user_id', $request->user()->id)->get();
        return $this->success(CategoryResource::collection($categories));
    }

    public function show($id)
    {
        $category = Category::whereNotNull('user_id')->with('questions')->findOrFail($id);
        if ($category->user_id !== auth('sanctum')->user()?->id) {
            return $this->error([], __('unauthorized'), 403);
        }
        return $this->success($category);
        // return $this->success(new CategoryResource($category));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return $this->error($e->errors(), $e->getMessage(), 422);
        }

        $category = Category::firstOrCreate([
            'title'    => $request->title,
            'user_id'  => $request->user()->id,
        ], [
            'content'  => $request->content,
            'image'    => $request->image,
            'is_temp'  => false,
        ]);

        return $this->success(new CategoryResource($category));
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title'   => 'sometimes|required|string|max:255',
                'content' => 'nullable|string',
                // 'image'   => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return $this->error($e->errors(), $e->getMessage(), 422);
        }

        $category = Category::findOrFail($id);

        // Ensure the user owns the category or has permission
        if ($category->user_id !== $request->user()->id) {
            return $this->error([], __('unauthorized'), 403);
        }

        $category->update($validated);

        return $this->success(new CategoryResource($category));
    }

    public function destroy(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        // Ensure the user owns the category or has permission
        if ($category->user_id !== $request->user()->id) {
            return $this->error([], __('unauthorized'), 403);
        }
        $category->delete();

        return $this->success([], __('category deleted successfully'));
    }
}
