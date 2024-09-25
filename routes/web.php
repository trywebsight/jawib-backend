<?php

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


Route::get('/', function () {
    return redirect('/admin');
});


////////////////
// Route::get('/login', function () {
//     return redirect()->away(env("FRONTEND_URL") . "/login");
// })->middleware('guest')->name('login');
