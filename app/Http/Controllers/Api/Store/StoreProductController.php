<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\Store\StoreProduct;
use Illuminate\Http\Request;

class StoreProductController extends Controller
{
    // Display a listing of products
    public function index()
    {
        $products = StoreProduct::all();
        return $this->success($products, __('products retrieved successfully'));
    }

    // Display the specified product
    public function show($id)
    {
        $product = StoreProduct::with('category')->find($id);
        if (!$product) {
            return $this->error([], __("invalid product id"), 422);
        }
        return $this->success($product, __('product retrieved successfully'));
    }
}
