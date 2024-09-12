<?php

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


Route::get('/', function () {
    return response()->make('
        <html>
        <head>
            <title>File Upload</title>
        </head>
        <body>
            <form action="/upload" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="' . csrf_token() . '">
                <label for="file">Select a file:</label>
                <input type="file" name="file" id="file" required>
                <br><br>
                <button type="submit">Upload File</button>
            </form>
        </body>
        </html>
    ');
    $user = User::first();

    // $user->deposit(2);
    // $user->withdraw(7);

    return $user->transactions;
    return $user->purchases;

    return view('welcome');
});

Route::post('/upload', function (Request $request) {
    if (!$request->hasFile('file')) {
        return response()->json(['error' => 'No file provided'], 400);
    }

    $file = $request->file('file');
    if (!$file->isValid()) {
        return response()->json(['error' => 'Invalid file'], 400);
    }

    $path = $file->store('uploads', 'public');
    return response()->json(['path' => $path], 200);
});



////////////////
Route::get('/login', function () {
    return redirect()->away(env("FRONTEND_URL") . "/login");
})->middleware('guest')->name('login');
