<?php

use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {

    $user = User::first();

    // $user->deposit(2);
    // $user->withdraw(7);

    return $user->transactions;
    return $user->purchases;

    return view('welcome');
});

Route::get('/login', function () {
    return redirect()->away(env("FRONTEND_URL") . "/login");
})->middleware('guest')->name('login');
