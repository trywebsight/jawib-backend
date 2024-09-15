<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        return $this->success(PageResource::collection(Page::where('is_published', true)->get()));
    }
    public function show($id)
    {
        $page = Page::where('is_published', true)->where('slug', $id)->first();
        if (!$page) {
            return $this->error([], __("page not found"), 404);
        }
        return $this->success(new PageResource($page));
    }
}
