<?php

use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {

    $user = User::first();

    // $user->deposit(2); // 0

    return $user->balance; // 0





    return view('welcome');
});
