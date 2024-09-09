<?php

use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {


    $filePath = 'test.txt';
    $fileContent = 'hello';

    // Attempt to upload the file to the DigitalOcean Spaces disk
    $result = Storage::disk('do')->put($filePath, $fileContent);

    return $result;





    $game = Game::with('questions', 'categories')->first();
    return $game;




    return view('welcome');
});
