<?php

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


Route::get('/', function () {
    return view('welcome');
});

<<<<<<< HEAD
=======

>>>>>>> a83dcdc33041832981c774347278c3fd8e1e1651
////////////////
// Route::get('/login', function () {
//     return redirect()->away(env("FRONTEND_URL") . "/login");
// })->middleware('guest')->name('login');
