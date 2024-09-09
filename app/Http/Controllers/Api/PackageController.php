<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use App\Models\Transaction;
use App\Services\TapPayment;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        return $this->success(PackageResource::collection(Package::get()));
    }
    public function show($id)
    {
        $package = Package::find($id);
        if (!$package) {
            return $this->error([], __("invalid package id"), 422);
        }
        return $this->success(new PackageResource($package));
    }
}
