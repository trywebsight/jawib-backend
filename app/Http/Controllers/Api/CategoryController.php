<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return $this->success(CategoryResource::collection(Category::get()));
    }
    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->error([], __("invalid category id"), 422);
        }
        return $this->success(new CategoryResource($category));
    }
}
