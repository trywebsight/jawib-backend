<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\Store\StoreCategory;
use Illuminate\Http\Request;

class StoreCategoryController extends Controller
{
    // Display a listing of categories
    public function index()
    {
        $categories = StoreCategory::all();
        return $this->success($categories, __('categories retrieved successfully'));
    }

    // Display the specified category with products
    public function show($id)
    {
        $category = StoreCategory::with('products')->find($id);
        if (!$category) {
            return $this->error([], __("invalid category id"), 422);
        }
        return $this->success($category, __('category retrieved successfully'));
    }
}
