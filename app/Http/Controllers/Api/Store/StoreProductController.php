<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\Store\StoreCategory;
use App\Models\Store\StoreProduct;
use Illuminate\Http\Request;

class StoreProductController extends Controller
{
    // Display a listing of products
    public function index()
    {
        $categories = StoreCategory::with('products')->get()->map(function ($category) {
            return [
                'id'    => $category->id,
                'title' => $category->title,
                'image' => media_url($category->image),
                'products' => $category->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'title' => $product->title,
                        'image' => media_url($product->image),
                        'price' => $product->price,
                    ];
                }),
            ];
        });
        return $this->success($categories, __('products retrieved successfully'));
    }

    // Display the specified product
    public function show($id)
    {
        $product = StoreProduct::find($id);
        if (!$product) {
            return $this->error([], __("invalid product id"), 422);
        }
        $product->image = media_url($product->image);
        return $this->success($product, __('product retrieved successfully'));
    }
}
